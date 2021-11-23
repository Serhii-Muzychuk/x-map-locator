<?php

namespace Drupal\x_map_locator;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * @TechDoc("Provides LocatorManager service.")
 *
 * @package Drupal\x_map_locator
 */
class LocatorManager implements LocatorManagerInterface {
  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   *
   * @TechDoc("The config factory service.")
   */
  private $configFactory;

  /**
   * @var \GuzzleHttp\ClientInterface
   *
   * @TechDoc("The http client service.")
   */
  private $httpClient;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   *
   * @TechDoc("The entity storage.")
   */
  private $markerStorage;

  /**
   * @TechDoc("The Json serializer.")
   *
   * @var \Drupal\Component\Serialization\Json
   */
  private $jsonSerialization;

  /**
   * @TechDoc("The Renderer.")
   *
   * @var \Drupal\Core\Render\Renderer
   */
  private $renderer;

  /**
   * @TechDoc("The Loger factory.")
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactory;

  /**
   * @TechDoc("Constructs a new LocatorManager object.")
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The http client
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager
   * @param \Drupal\Component\Serialization\Json $jsonSerialization
   *   The json serializer
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    ClientInterface $httpClient,
    EntityTypeManagerInterface $entityTypeManager,
    Json $jsonSerialization,
    Renderer $renderer,
    LoggerChannelFactoryInterface $loggerFactory
  ) {
    $this->configFactory = $configFactory;
    $this->httpClient = $httpClient;
    $this->markerStorage = $entityTypeManager->getStorage('x_map_locator_marker_type');
    $this->jsonSerialization = $jsonSerialization;
    $this->renderer = $renderer;
    $this->loggerFactory = $loggerFactory;
  }

  /**
   * {@inheritDoc}.
   */
  public function getLocationByAddress($address, $additionalValues = NULL) {
    $response = [];
    $locator_settings = $this->configFactory->get('x_map_locator.settings');
    $requestUrl = $locator_settings->get('google_geocoder_api_url');
    $googleKey = $locator_settings->get('google_api_key');

    if (!empty($googleKey) && !empty($address)) {
      $address = rawurlencode($address);
      $requestUrl .= '?address=' . $address;
      if (!empty($additionalValues)) {
        foreach ($additionalValues as $value) {
          if (!empty($value)) {
            $requestUrl .= '%2C%20' . $value;
          }
        }
      }
      $requestUrl .= '&key=' . $googleKey;
      // Get location by address parameters from the geocode api.
      try {
        $result = $this->jsonSerialization->decode(
          $this->httpClient->request('GET', $requestUrl)->getBody()
        );
        if (!empty($result)
          && !empty($result = reset($result['results']))
          && !empty($location = $result['geometry']['location'])) {
          return $location;
        }
      }
      catch (GuzzleException $e) {
        $message = $e->getMessage();
        $this->loggerFactory->get('x_map_locator')->notice($message);

        return [];
      }
    }

    return $response;
  }

  /**
   * {@inheritDoc}.
   */
  public function getFullAddress($address1, $address2 = NULL) {
    if (!empty($address1)) {
      if (!empty($address2)) {
        $address1 .= ' ' . $address2;
      }

      return $address1;
    }

    return FALSE;
  }

  /**
   * {@inheritDoc}.
   */
  public function getInternalPinImageUrl($type) {
    $markerEntities = $this->markerStorage->loadMultiple();
    $markerInternalIconUrl = [];
    if (!empty($markerEntities)) {
      foreach ($markerEntities as $markerEntity) {
        $url = $markerEntity->getInternalIconRealUrl();
        $markerInternalIconUrl[$markerEntity->id()] = $url;
      }
    }

    return $markerInternalIconUrl[$type];
  }

  /**
   * {@inheritDoc}.
   */
  public function getPinImageUrl($type) {
    $markerEntities = $this->markerStorage->loadMultiple();
    $markerIconUrl = [];
    if (!empty($markerEntities)) {
      foreach ($markerEntities as $markerEntity) {
        $url = $markerEntity->getIconRealUrl();
        $markerIconUrl[$markerEntity->id()] = $url;
      }
    }

    return $markerIconUrl[$type];
  }

  /**
   * {@inheritDoc}.
   */
  public function getRenderedMarkers($entities, $address = NULL) {
    $result = [];
    if (empty($entities)) {
      return [
        'render_markers' => '',
        'render_output' => '',
        'results' => '',
      ];
    }
    foreach ($entities as $entity) {
      $markerId = $entity->get('qualification')->value;
      $markerLabel = $this->t('Doctor');
      if (!empty($markerId)) {
        $type = $this->markerStorage->load($markerId);
        if (!empty($type)) {
          $markerLabel = $type->label();
        }
      }

      $site = $entity->get('website')->getString();
      if (!empty($site)) {
        if (!filter_var($site, FILTER_VALIDATE_URL)) {
          $site = 'http://' . $site;
        }
      }

      $result[] = [
        'address' => $entity->getAddress(),
        'zip' => $entity->getZipcode(),
        'id' => $entity->id(),
        'city' => $entity->get('city')->getString(),
        'site' => $site,
        'area' => $entity->get('area')->getString(),
        'practice' => $entity->get('practice')->getString(),
        'phone' => $entity->get('phone')->getString(),
        'type' => $markerLabel,
        'email' => $entity->get('email')->getString(),
        'pin_internal_icon_url' => $this->getInternalPinImageUrl($entity->get('qualification')->value),
        'pin_icon_url' => $this->getPinImageUrl($entity->get('qualification')->getString()),
        'schedule_appointment' => $entity->get('schedule_appointment')->getString(),
        'name' => $entity->getName(),
        'first_name' => $entity->get('first_name')->getString(),
        'last_name' => $entity->get('last_name')->getString(),
        'name_prefix' => $entity->get('name_prefix')->getString(),
        'lat' => $entity->get('location_latitude')->getString(),
        'lng' => $entity->get('location_longitude')->getString(),
      ];

      if ($address) {
        foreach ($result as $key => $item) {
          foreach ($address as $addressValue) {
            if (in_array($addressValue, $item)) {
              $out = array_splice($result, $key, 1);
              array_splice($result, 0, 0, $out);
            }
          }
        }
      }
    }
    $mapResultsBuild = [
      '#theme' => 'x_map_results',
      '#entities' => $result,
      '#view_all' => count($result),
    ];
    $markersBuild = [
      '#theme' => 'x_map_info_block',
      '#entities' => $result,
    ];

    return [
      'render_markers' => $this->renderer->render($markersBuild),
      'render_output' => $this->renderer->render($mapResultsBuild),
      'results' => $result,
    ];
  }

}
