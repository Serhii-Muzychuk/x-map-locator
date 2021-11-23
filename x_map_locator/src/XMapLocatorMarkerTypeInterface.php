<?php

namespace Drupal\x_map_locator;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * @TechDoc("Provides an interface defining the XMapLocatorMarkerType
 * entity.")
 */
interface XMapLocatorMarkerTypeInterface extends ConfigEntityInterface {

  /**
   * @TechDoc("Get the label.")
   *
   * @return null|string
   *   The description of the entity, or NULL if there is no label defined
   */
  public function getLabel();

  /**
   * @TechDoc("Get the description.")
   *
   * @return null|string
   *   The description of the entity, or NULL if there is no description defined
   */
  public function getDescription();

  /**
   * @TechDoc("Get the real icon url.")
   *
   * @return null|string
   *   The description of the entity, or NULL if there is no icon defined
   */
  public function getIconRealUrl();

  /**
   * @TechDoc("Get the internal real icon url.")
   *
   * @return null|string
   *   The description of the entity, or NULL if there is no internal icon
   *                     defined
   */
  public function getInternalIconRealUrl();

  /**
   * @TechDoc("Get the icon file id.")
   *
   * @return null|string
   *   The icon of the entity, or NULL if there is no icon defined
   */
  public function getIcon();

  /**
   * @TechDoc("Get the internal icon file id.")
   *
   * @return null|string
   *   The icon of the entity, or NULL if there is no icon defined
   */
  public function getInternalIcon();

  /**
   * @TechDoc("Get the icon name.")
   *
   * @return null|string
   *   The icon of the entity, or NULL if there is no icon defined
   */
  public function getIconName();

  /**
   * @TechDoc("Get the icon image tag.")
   *
   * @return null|string
   *   The icon of the entity, or NULL if there is no icon defined
   */
  public function getIconImage();

  /**
   * @TechDoc("Get the internal icon image tag.")
   *
   * @return null|string
   *   The icon of the entity, or NULL if there is no internal icon defined
   */
  public function getInternalIconImage();

  /**
   * @TechDoc("Get the icon of the marker type for the form.")
   *
   * @return null|string
   *   The icon of the entity, or NULL if there is no icon defined
   */
  public function getIconForForm();

  /**
   * @TechDoc("Get the internal icon of the marker type for the form.")
   *
   * @return null|string
   *   The icon of the entity, or NULL if there is no internal icon defined
   */
  public function getInternalIconForForm();

  /**
   * @TechDoc("Get the weight of the marker type.")
   *
   * @return null|int|string
   *   The weight of the entity, or NULL if there is no weight defined
   */
  public function getWeight();

  /**
   * @TechDoc("Get icon file url.")
   *
   * @return null|string
   *   The icon file url of the entity, or NULL if there is no url defined
   */
  public function getIconFileUrl();

}
