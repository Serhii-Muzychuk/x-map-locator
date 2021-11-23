<?php

namespace Drupal\x_map_locator\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @TechDoc("Provides XMapLocatorSetting form.")
 *
 * @package Drupal\x_map_locator\Form
 */
class XMapLocatorSettingForm extends ConfigFormBase {
  /**
   * @TechDoc("Config settings.")
   *
   * @var string
   */
  public const SETTINGS = 'x_map_locator.settings';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->markerStorage = $container
      ->get('entity_type.manager')
      ->getStorage('x_map_locator_marker_type');
    $instance->moduleHandler = $container->get('module_handler');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'x_map_locator_setting_form';
  }

  /**
   * @TechDoc("Provides build form with all needed data for Google map.")
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $form['google_set'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Options for Google Map.'),
    ];
    $form['google_set']['google_api_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Google API Url'),
      '#default_value' => $config->get('google_api_url'),
      '#required' => TRUE,
    ];
    $form['google_set']['google_geocoder_api_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Google Geocoder API Url'),
      '#default_value' => $config->get('google_geocoder_api_url'),
      '#required' => TRUE,
    ];
    $url = Url::fromUri('https://developers.google.com/maps/documentation/javascript/get-api-key', ['_target' => 'blank']);
    $link = Link::fromTextAndUrl($this->t('See more'), $url);

    $form['google_set']['google_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Key API'),
      '#default_value' => $config->get('google_api_key'),
      '#required' => TRUE,
      '#description' => $this->t('Google API docs. @link', ['@link' => $link->toString()]),
    ];
    $form['google_set']['zoom'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Zoom'),
      '#default_value' => !empty($config->get('zoom')) ? $config->get('zoom') : 16,
      '#description' => $this->t('Default zoom for the map.'),
    ];
    $form['google_set']['def_position'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Start coordinates for Google Map.'),
      '#attributes' => [
        'style' => ['display' => 'flex'],
      ],
    ];
    $form['google_set']['def_position']['latitude'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Map start position latitude'),
      '#default_value' => !empty($config->get('def_position')) && !empty($config->get('def_position')['latitude']) ? $config->get('def_position')['latitude'] : '42.202583',
      '#description' => $this->t('Start latitude coordinates for the map. Default: 42.202583'),
    ];
    $form['google_set']['def_position']['longitude'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Map start position longitude'),
      '#default_value' => !empty($config->get('def_position')) && !empty($config->get('def_position')['longitude']) ? $config->get('def_position')['longitude'] : '-104.527926',
      '#description' => $this->t('Start longitude coordinates for the map. Default: -104.527926'),
    ];
    $form['content']['hubspot_portal_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hubspot Portal Id'),
      '#default_value' => $config->get('hubspot_portal_id'),
    ];
    $form['content']['hubspot_form_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hubspot Form Id'),
      '#default_value' => $config->get('hubspot_form_id'),
    ];
    $form['content']['hints_hide'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hints block hidden'),
      '#default_value' => $config->get('hints_hide'),
    ];

    if ($this->moduleHandler->moduleExists('x_map_locator_sales_rep')) {
      $form['content']['number_limit'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Territory code limit'),
        '#default_value' => $config->get('number_limit'),
      ];
    }

    $form['pins'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Marker types.'),
    ];
    $markers = $this->getMarkerTypesList();
    if (!empty($markers)) {
      $index = 0;
      foreach ($markers as $key => $marker) {
        $form['pins'][$key] = [
          '#type' => 'fieldset',
          '#title' => $this->t(
            'Type No.%number: %title.',
            [
              '%number' => ++$index,
              '%title' => $marker,
            ]
          ),
        ];
      }
      $form['pins']['list_link'] = [
        '#type' => 'markup',
        '#markup' => Link::fromTextAndUrl($this->t('View type list'), Url::fromRoute('entity.x_map_locator_marker_type.collection'))->toString(),
      ];
    }
    else {
      $form['pins']['empty'] = [
        '#type' => 'markup',
        '#markup' => $this->t('Marker Type does not exist, to create %link.', [
          '%link' => Link::fromTextAndUrl($this->t('click here'), Url::fromRoute('entity.x_map_locator_marker_type.collection'))->toString(),
        ]),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @TechDoc("Save values to settings.")
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $startCoordinates = [
      'latitude' => $form_state->getValue('latitude'),
      'longitude' => $form_state->getValue('longitude'),
    ];

    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)

      // Set the submitted configuration setting.
      ->set('google_api_key', $form_state->getValue('google_api_key'))
      ->set('google_geocoder_api_url', $form_state->getValue('google_geocoder_api_url'))
      ->set('zoom', $form_state->getValue('zoom'))
      ->set('def_position', $startCoordinates)
      ->set('hubspot_form_id', $form_state->getValue('hubspot_form_id'))
      ->set('button_text', $form_state->getValue('button_text'))
      ->set('google_api_url', $form_state->getValue('google_api_url'))
      ->set('hubspot_form_id', $form_state->getValue('hubspot_form_id'))
      ->set('hubspot_portal_id', $form_state->getValue('hubspot_portal_id'))
      ->set('hints_hide', $form_state->getValue('hints_hide'))
      ->save();

    if ($this->moduleHandler->moduleExists('x_map_locator_sales_rep')) {
      $this->configFactory->getEditable(static::SETTINGS)
        ->set('number_limit', $form_state->getValue('number_limit'))
        ->save();
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * @TechDoc("Provides Marker types list.")
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @return array
   */
  private function getMarkerTypesList() {
    $markerEntities = $this->markerStorage->loadMultiple();
    $markerList = [];
    if (!empty($markerEntities)) {
      foreach ($markerEntities as $key => $markerEntity) {
        $markerList[$key] = $markerEntity->label();
      }
    }

    return $markerList;
  }

}
