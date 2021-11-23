<?php

namespace Drupal\x_map_locator\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @TechDoc("Provides XMapLocatorAdvancedSearchForm form.")
 */
class XMapLocatorAdvancedSearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->subdivisionRepository = $container->get('address.subdivision_repository');
    $instance->renderer = $container->get('renderer');
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
    return 'map_locator_advanced_search_form';
  }

  /**
   * @TechDoc("Build XMapLocatorAdvancedSearchForm form.")
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $usaAdministrativeAreas = $this->subdivisionRepository->getList(['US']);
    $canadaAdministrativeAreas = $this->subdivisionRepository->getList(['CA']);

    foreach ($canadaAdministrativeAreas as $key_area => $area) {
      $usaAdministrativeAreas[$key_area] = $area;
    }
    array_unshift($usaAdministrativeAreas, $this->t('Select State*'));
    $form['search_by'] = [
      '#type' => 'radios',
      '#options' => [
        'location' => $this->t('Location'),
        'doctor' => $this->t('Doctor'),
      ],
      '#attributes' => [
        'class' => [
          'search-by-tab',
        ],
      ],
      '#default_value' => 'location',
    ];
    $form['doctor_form'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#weight' => 1,
      '#attributes' => [
        'class' => [
          'advanced-map-locator-doctor-form',
        ],
      ],
      '#states' => [
        'visible' => [
          ':input[name="search_by"]' => [
            'value' => 'doctor',
          ],
        ],
      ],
    ];
    $form['doctor_form']['name'] = [
      '#type' => 'textfield',
      '#placeholder' => $this->t('Doctor Name'),
      '#attributes' => [
        'class' => [
          'field-form-size-one',
          'advanced-map-locator-doctor-name',
        ],
      ],
    ];
    $form['doctor_form']['state'] = [
      '#type' => 'select',
      '#options' => $usaAdministrativeAreas,
      '#default value' => 0,
      '#attributes' => [
        'class' => [
          'field-form-size-one',
          'advanced-map-locator-doctor-state',
          'chosen-disable',
        ],
      ],
    ];
    $form['location_form'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#weight' => 2,
      '#attributes' => [
        'class' => [
          'advanced-map-locator-edit-location-form',
        ],
      ],
      '#states' => [
        'visible' => [
          ':input[name="search_by"]' => [
            'value' => 'location',
          ],
        ],
      ],
    ];
    $form['location_form']['address'] = [
      '#type' => 'textfield',
      '#placeholder' => $this->t('Address'),
      '#attributes' => [
        'class' => [
          'field-form-size-one',
          'advanced-map-locator-location-address',
        ],
      ],
      '#weight' => 1,
    ];
    $form['location_form']['zipcode'] = [
      '#type' => 'textfield',
      '#placeholder' => $this->t('Zip Code'),
      '#attributes' => [
        'class' => [
          'field-form-size-one',
          'advanced-map-locator-location-zip',
        ],
      ],
      '#weight' => 6,
    ];
    $form['location_form']['state'] = [
      '#type' => 'select',
      '#options' => $usaAdministrativeAreas,
      '#default value' => 0,
      '#attributes' => [
        'class' => [
          'field-form-size-one',
          'advanced-map-locator-location-state',
          'chosen-disable',
        ],
      ],
      '#weight' => 5,
    ];
    $form['location_form']['suite'] = [
      '#type' => 'textfield',
      '#placeholder' => $this->t('Suite'),
      '#weight' => 2,
      '#attributes' => [
        'class' => [
          'field-form-size-divide-two',
          'advanced-map-locator-location-suite',
        ],
      ],
    ];
    $form['location_form']['city'] = [
      '#type' => 'textfield',
      '#placeholder' => $this->t('City'),
      '#weight' => 3,
      '#attributes' => [
        'class' => [
          'field-form-size-divide-two',
          'advanced-map-locator-location-city',
        ],
      ],
    ];
    $form['submit'] = [
      '#prefix' => '<div id="adv-search-btn-wrapper">',
      '#suffix' => '</div>',
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => '::ajaxSearchBuildForm',
        'event' => 'click',
        'progress' => ['type' => 'none'],
      ],
      '#weight' => 3,
      '#attributes' => [
        'class' => [
          'locator-btn',
          'advanced-search-button',
        ],
      ],
    ];
    $form['#theme'] = 'map_locator_advanced_search_form';

    return $form;
  }

  /**
   * @TechDoc("Default submit XMapLocatorAdvancedSearchForm form method.")
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * @TechDoc("Ajax submit for default search on map.")
   */
  public function ajaxSearchBuildForm(array &$form, FormStateInterface $form_state) {
    $values = [];
    $renderMarkers = [
      'render_markers' => '',
      'render_output' => '',
      'results' => '',
    ];
    $response = new AjaxResponse();
    $searchBy = $form_state->getValue('search_by');

    switch ($searchBy) {
      case 'location':
        $values = $form_state->getValue('location_form');

        break;

      case 'doctor':
        $values = $form_state->getValue('doctor_form');

        break;
    }

    if (empty($values['state'])) {
      return $response;
    }
    $query = $this->doctorStorage->getQuery();

    if (!empty($values['address'])) {
      $query->condition('address1', '%' . $values['address'] . '%', 'LIKE');
    }

    if (!empty($values['zipcode'])) {
      $query->condition('zip', $values['zipcode'] . '%', 'LIKE');
    }

    if (!empty($values['city'])) {
      $query->condition('city', '%' . $values['city'] . '%', 'LIKE');
    }

    if (!empty($values['state'])) {
      $query->condition('area', $values['state']);
    }

    if (!empty($values['suite'])) {
      if (strpos(strtolower($values['suite']), 'ste') !== FALSE) {
        $group = $query->orConditionGroup()
          ->condition('address1', '%' . $values['suite'] . '%', 'LIKE')
          ->condition('address2', '%' . $values['suite'] . '%', 'LIKE');
      }
      else {
        $group = $query->orConditionGroup()
          ->condition('address1', '% ste %' . $values['suite'] . '%', 'LIKE')
          ->condition('address2', '% ste %' . $values['suite'] . '%', 'LIKE');
      }
      $query->condition($group);
    }

    if (!empty($values['name'])) {
      if (strpos($values['name'], ' ') !== FALSE) {
        $separated_name = explode(' ', $values['name']);
        $query->condition('first_name', $separated_name[0]);
        $query->condition('last_name', $separated_name[1] . '%', 'LIKE');
      }
      else {
        $group = $query->orConditionGroup()
          ->condition('first_name', '%' . $values['name'] . '%', 'LIKE')
          ->condition('last_name', '%' . $values['name'] . '%', 'LIKE');
        $query->condition($group);
      }
    }
    $query->condition('status', TRUE);
    $resultIds = $query->execute();
    if (!empty($resultIds)) {
      $entities = $this->doctorStorage->loadMultiple($resultIds);
      $renderMarkers = $this->locatorManager->getRenderedMarkers($entities);
    }
    $build['#attached']['library'][] = 'x_map_locator/locator';
    $build['#attached']['drupalSettings']['items'] = !empty($renderMarkers['results']) ? $renderMarkers['results'] : 'empty';
    $response->addAttachments($build['#attached']);
    $response->addCommand(new HtmlCommand('#map-result-box', ''));
    $response->addCommand(new AppendCommand('#map-result-box', $renderMarkers['render_output']));
    $response->addCommand(new AppendCommand('#map-result-box', $renderMarkers['render_markers']));

    return $response;
  }

}
