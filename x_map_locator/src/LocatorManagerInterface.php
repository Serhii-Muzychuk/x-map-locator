<?php

namespace Drupal\x_map_locator;

/**
 * @TechDoc("Provides an interface for LocatorManager service").
 */
interface LocatorManagerInterface {

  /**
   * @TechDoc("Get doctor location by doctor entity address values")
   *
   * @param string $address
   *   The doctor address
   * @param array $additionalValues
   *   The doctor additional address values
   *
   * @return array|false
   *   The doctor location array or FALSE otherwise
   */
  public function getLocationByAddress(string $address, array $additionalValues);

  /**
   * @TechDoc("Get doctor full address by the addresses fields values")
   *
   * @param string $address1
   *   The doctor first address value
   * @param string $address2
   *   The doctor second address value
   *
   * @return false|string
   *   The doctor full address or FALSE otherwise
   */
  public function getFullAddress(string $address1, string $address2);

  /**
   * @TechDoc("Get internal pin icon image for different types of qualification")
   *
   * @param string $type
   *   The doctor qualification
   *
   * @return mixed
   */
  public function getInternalPinImageUrl(string $type);

  /**
   * @TechDoc("Get pin icon image for different types of qualification")
   *
   * @param string $type
   *   The doctor qualification
   *
   * @return mixed
   */
  public function getPinImageUrl(string $type);

  /**
   * @TechDoc("Provides rendered markers and infoWindows")
   *
   * @param array $entities
   *   The array with entities to render
   *
   * @param array $address
   *   The array with address
   * @return mixed
   */
  public function getRenderedMarkers(array $entities, array $address);

}
