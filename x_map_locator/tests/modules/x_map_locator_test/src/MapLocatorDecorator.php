<?php

namespace Drupal\x_map_locator_test;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\Renderer;
use Drupal\x_map_locator\LocatorManager;
use GuzzleHttp\ClientInterface;

/**
 * Class MapLocatorDecorator.
 *
 * LocatorManager Decorator for tests purposes.
 *
 * @package Drupal\x_map_locator_test
 */
class MapLocatorDecorator extends LocatorManager {

  /**
   * Original service object.
   *
   * @var \Drupal\x_map_locator\LocatorManager
   */
  protected $originalService;

  /**
   * @TechDoc("Constructs a new LocatorManager object.")
   *
   * @param \Drupal\x_map_locator\LocatorManager $originalService
   *   The config factory
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
    LocatorManager $originalService,
    ConfigFactoryInterface $configFactory,
    ClientInterface $httpClient,
    EntityTypeManagerInterface $entityTypeManager,
    Json $jsonSerialization,
    Renderer $renderer,
    LoggerChannelFactoryInterface $loggerFactory
  ) {
    $this->originalService = $originalService;
    parent::__construct(
      $configFactory,
      $httpClient,
      $entityTypeManager,
      $jsonSerialization,
      $renderer,
      $loggerFactory
    );
  }

  /**
   * {@inheritDoc}.
   */
  public function getLocationByAddress($address, $additionalValues = NULL) {
    return [
      'lat' => 47.6105427,
      'lng' => -122.142426,
    ];
  }

}
