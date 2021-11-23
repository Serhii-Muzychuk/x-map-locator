<?php

namespace Drupal\x_map_locator\Plugin\Block;

use Drupal\Component\Utility\Random;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @TechDoc("Provides a block with a X map Locator search.")
 *
 * @Block(
 *   id = "x_map_locator_search_block",
 *   admin_label = @Translation("X Map Locator Search block"),
 *   category = @Translation("X Braces"),
 * )
 */
class XMapLocatorSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->configFactory = $container->get('config.factory');
    $instance->formBuilder = $container->get('form_builder');
    $instance->renderer = $container->get('renderer');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->languageManager = $container->get('language_manager');
    $instance->moduleHandler = $container->get('module_handler');

    return $instance;
  }

  /**
   * @TechDoc("Provides the block build.")
   *
   * {@inheritdoc}
   */
  public function build(): array {
    $xLocator = $this->configFactory->get('x_map_locator.settings');
    $googleApiKey = $xLocator->get('google_api_key');
    $zoom = $xLocator->get('zoom');
    $center = $xLocator->get('def_position');
    $googleApiUrl = $xLocator->get('google_api_url');
    $portalId = $xLocator->get('hubspot_portal_id');
    $formId = $xLocator->get('hubspot_form_id');
    $hints_hide = $xLocator->get('hints_hide');

    $sales_rep_controller = [];
    if ($this->moduleHandler->moduleExists('x_map_locator_sales_rep')) {
      $sales_rep_controller = Url::fromRoute('x_map_locator_sales_rep.check_zip')->toString();
    }

    $form = $this->formBuilder->getForm('\Drupal\x_map_locator\Form\XLocatorSearchForm');
    $advanceForm = $this->formBuilder->getForm('\Drupal\x_map_locator\Form\XMapLocatorAdvancedSearchForm');
    $markerTypes = $this->entityTypeManager->getStorage('x_map_locator_marker_type')->loadMultiple();
    $markers = [];
    if (!empty($markerTypes)) {
      foreach ($markerTypes as $markerType) {
        $markers[] = [
          'title' => $markerType->label(),
          'description' => $markerType->getDescription(),
          'icon' => $markerType->getIconFileUrl(),
        ];
      }
    }

    $sourceConfiguration = $this->configFactory->get('media.type.hubspot_form')->get('source_configuration');
    $script = $sourceConfiguration['hubspot_form_script_src'];

    $scheduled_appointment = [
      '#theme' => 'x_hubspot_form',
      '#portal_id' => $portalId,
      '#form_id' => $formId,
      '#target' => $portalId . '-' . $formId,
      '#script' => $script,
    ];

    $random = new Random();
    $blockId = 'map-' . $random->name('8');
    $wrapperId = 'wrapper-' . $random->name('8');

    return [
      '#theme' => 'x_map_container',
      '#map_container' => [
        'form' => $this->renderer->render($form),
        'advance_form' => $this->renderer->render($advanceForm),
        'markers' => $markers,
        'block_id' => $blockId,
        'wrapper_id' => $wrapperId,
        'scheduled_appointment' => $scheduled_appointment,
        'hints_hide' => $hints_hide,
      ],
      '#attached' => [
        'library' => [
          'x_map_locator/locator',
          'x_map_locator/x_map_locator',
        ],
        'drupalSettings' => [
          'sales_rep_controller' => $sales_rep_controller,
          'api_key' => $googleApiKey,
          'google_api_url' => $googleApiUrl,
          'zoom' => $zoom,
          'center' => $center['latitude'] . ', ' . $center['longitude'],
          'block_id' => $blockId,
          'wrapper_id' => $wrapperId,
        ],
      ],
    ];
  }

}
