<?php

namespace Drupal\x_map_locator\Form;

use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @TechDoc("Provides XLocatorSearchForm form.")
 *
 * @package Drupal\x_map_locator\Form
 */
class XLocatorSearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = new static();
    $instance->db = $container->get('database');
    $instance->markerStorage = $container->get('entity_type.manager')->getStorage('x_map_locator_marker_type');
    $instance->doctorStorage = $container->get('entity_type.manager')->getStorage('x_map_locator_doctor');
    $instance->locatorManager = $container->get('x_map_locator.locator_manager');

    return $instance;
  }

  /**
   * @TechDoc("Provides get form id method.")
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'locator_search_form';
  }

  /**
   * @TechDoc("Build XLocatorSearchForm form.")
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['inline_form'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['inline-search-wrapper'],
      ],
    ];
    $form['inline_form']['address'] = [
      '#type' => 'textfield',
      '#size' => 50,
      '#placeholder' => $this->t('Enter location, city or zip code'),
      '#attributes' => [
        'class' => [
          'text-field',
          'field-address',
        ],
      ],
    ];
    $form['inline_form']['radius'] = [
      '#type' => 'select',
      '#options' => [
        20 => $this->t('Radius 20 mile'),
        50 => $this->t('Radius 50 mile'),
        100 => $this->t('Radius 100 mile'),
        150 => $this->t('Radius 150 mile'),
      ],
      '#default_value' => 20,
      '#attributes' => [
        'class' => [
          'field-radius',
        ],
      ],
    ];
    $form['inline_form']['submit'] = [
      '#type' => 'submit',
      '#ajax' => [
        'callback' => '::ajaxSearchBuildForm',
        'event' => 'click',
        'wrapper' => 'map-result-box',
        'progress' => ['type' => 'none'],
      ],
      '#attributes' => [
        'class' => ['search-btn'],
      ],
    ];
    $form['inline_form']['advance_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'second-search-form-adv-btn-wrapper',
      ],
    ];
    $form['inline_form']['advance_wrapper']['advance_button'] = [
      '#type' => 'link',
      '#title' => $this->t('Advanced Search'),
      '#url' => Url::fromRoute('<current>'),
      '#attributes' => [
        'id' => 'second-search-form-adv-btn',
        'class' => [
          'locator-btn',
          'adv-second-search-btn',
          'locator-btn-link',
        ],
      ],
    ];
    $form['results_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'map-result-box',
      ],
    ];

    return $form;
  }

  /**
   * @TechDoc("Default submit XLocatorSearchForm form method.")
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * @TechDoc("Ajax submit for default search on map.")
   */
  public function ajaxSearchBuildForm(array &$form, FormStateInterface $form_state) {
    $address = $form_state->getValue('address');
    $radius = $form_state->getValue('radius');
    $renderMarkers = [
      'render_markers' => '',
      'render_output' => '',
      'results' => '',
    ];
    if (!empty($radius) && !empty($address)) {
      $coordinates = $this->locatorManager->getLocationByAddress($address);
      if (!empty($coordinates)) {
        $doctorResults = $this->getProximityDoctors($radius, $coordinates['lat'], $coordinates['lng']);
        if (!empty($doctorResults)) {
          $entities = $this->doctorStorage->loadMultiple($doctorResults);
          $renderMarkers = $this->locatorManager->getRenderedMarkers($entities, [$address]);
        }
      }
    }
    else {
      return $form['results_container'];
    }
    $form['results_container']['#attached']['library'][] = 'x_map_locator/locator';
    $form['results_container']['#attached']['drupalSettings']['items'] = !empty($renderMarkers['results']) ? $renderMarkers['results'] : 'empty';
    $form['results_container']['sidebar-container']['#markup'] = $renderMarkers['render_output'];
    $form['results_container']['markers-container']['#markup'] = $renderMarkers['render_markers'];

    return $form['results_container'];
  }

  /**
   * @TechDoc("Get proximity doctors.")
   *
   * @param int|string $radius
   *   The proximity radius
   * @param string $lat
   *   The doctor latitude
   * @param string $lon
   *   The doctor longitude
   *
   * @return array
   *   Return array with proximity doctors or FALSE if exception
   */
  public function getProximityDoctors($radius, string $lat, string $lon): array {
    $response = [];
    if (empty($radius) || empty($lat) || empty($lon)) {
      return $response;
    }

    // Constant related to the radius of the Earth
    $earthsRadius = 3958.9394;

    // Spherical Law of Cosines
    $distanceFormula = "{$earthsRadius} * ACOS( SIN(RADIANS(location_latitude)) * SIN(RADIANS({$lat})) + COS(RADIANS(location_longitude - {$lon})) * COS(RADIANS(location_latitude)) * COS(RADIANS({$lat})) )";

    try {
      $query = $this->db->select('x_map_locator_doctor', 'eml')
        ->fields('eml', ['id']);
      $query->addExpression($distanceFormula);
      $query->where($distanceFormula . ' < ' . $radius);
      $query->condition('status', TRUE);
      $results = $query->execute()->fetchAll();
      if (!empty($results)) {
        $results = json_decode(json_encode($results), TRUE);

        // Sorting by the distance from the center search.
        usort($results, function ($a, $b) {
          if (isset($a['expression']) && isset($b['expression'])) {
            return $a['expression'] <=> $b['expression'];
          }
        });
      }
    }
    catch (DatabaseExceptionWrapper $e) {
      $message = $e->getMessage();
      $this->getLogger('x_map_locator')->notice($message);

      return [];
    }
    if (!empty($results) && is_array($results)) {
      foreach ($results as $result) {
        if (!empty($result['id'])) {
          $response[] = $result['id'];
        }
      }
    }

    return $response;
  }

}
