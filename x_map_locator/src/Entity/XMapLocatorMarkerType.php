<?php

namespace Drupal\x_map_locator\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Url;
use Drupal\x_map_locator\xMapLocatorMarkerTypeInterface;
use Drupal\file\Entity\File;

/**
 * @TechDoc("Defines the XMapLocatorMarkerType entity.")
 *
 * @ingroup XMapLocator
 *
 * @ConfigEntityType(
 *   id = "x_map_locator_marker_type",
 *   label = @Translation("X Map Locator Marker Type entity"),
 *   handlers = {
 *     "list_builder" = "Drupal\x_map_locator\Controller\XMapLocatorMarkerTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\x_map_locator\Form\XMapLocatorMarkerTypeForm",
 *       "edit" = "Drupal\x_map_locator\Form\XMapLocatorMarkerTypeForm",
 *       "delete" = "Drupal\x_map_locator\Form\XMapLocatorMarkerTypeDeleteForm",
 *     },
 *   },
 *   config_prefix = "x_map_locator_marker_type",
 *   admin_permission = "administer x map locator",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "weight" = "weight",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "weight",
 *     "description" = "description",
 *     "icon" = "icon",
 *     "internal_icon" = "internal_icon",
 *   },
 *   links = {
 *     "collection" = "/admin/x/x-map-locator/setting/marker-types",
 *     "add-form" = "/admin/x/x-map-locator/setting/marker-types/add",
 *     "edit-form" =
 *   "/admin/x/x-map-locator/setting/marker-types/{x_map_locator_marker_type}/edit",
 *     "delete-form" =
 *   "/admin/x/x-map-locator/setting/marker-types/{x_map_locator_marker_type}/delete",
 *   },
 * )
 */
class XMapLocatorMarkerType extends ConfigEntityBase implements XMapLocatorMarkerTypeInterface {
  /**
   * The Matker Type ID.
   *
   * @var int|string
   */
  protected $id;

  /**
   * The Marker Type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The position weight (not physical) of this Marker Type.
   */
  protected $weight;

  /**
   * The description of this marker type.
   *
   * @var string
   */
  protected $description = '';

  /**
   * The icon of this marker type.
   *
   * @var mixed
   */
  protected $icon;

  /**
   * The internal icon of this marker type.
   *
   * @var mixed
   */
  protected $internal_icon;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  private $renderer;

  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    $this->renderer = \Drupal::service('renderer');
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel(): string {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): ?string {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconRealUrl() {
    $file = File::load($this->getIcon());
    if (!empty($file)) {
      $uri = $file->getFileUri();

      return Url::fromUri(file_create_url($uri))->toString();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getInternalIconRealUrl() {
    $file = File::load($this->getInternalIcon());
    if (!empty($file)) {
      $uri = $file->getFileUri();

      return Url::fromUri(file_create_url($uri))->toString();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getIcon() {
    return $this->icon;
  }

  /**
   * {@inheritdoc}
   */
  public function getInternalIcon(): string {
    return $this->internal_icon;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconImage() {
    $file = File::load($this->icon);
    if (!empty($file)) {
      $path = $file->getFileUri();
      $iconBuild = [
        '#theme' => 'image_style',
        '#width' => 40,
        '#height' => 40,
        '#style_name' => 'medium',
        '#uri' => $path,
      ];
      $renderer = $this->renderer;
      $renderer->addCacheableDependency($iconBuild, $file);

      return $renderer->render($iconBuild);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getInternalIconImage() {
    $file = File::load($this->internal_icon);
    if (!empty($file)) {
      $path = $file->getFileUri();
      $iconBuild = [
        '#theme' => 'image_style',
        '#width' => 40,
        '#height' => 40,
        '#style_name' => 'medium',
        '#uri' => $path,
      ];
      $renderer = $this->renderer;
      $renderer->addCacheableDependency($iconBuild, $file);

      return $renderer->render($iconBuild);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconFileUrl() {
    $file = File::load($this->icon);
    if (!empty($file)) {
      return $file->getFileUri();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconForForm() {
    return !empty($this->icon) ? [$this->icon] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getInternalIconForForm() {
    return !empty($this->internal_icon) ? [$this->internal_icon] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconName() {
    $file = File::load($this->icon);
    if (!empty($file)) {
      return $file->getFilename();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

}
