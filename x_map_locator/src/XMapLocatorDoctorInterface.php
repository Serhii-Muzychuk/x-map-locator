<?php

namespace Drupal\x_map_locator;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * @TechDoc("Provides an interface defining an X Map Locator Doctor
 * entity.")
 */
interface XMapLocatorDoctorInterface extends ContentEntityInterface {

  /**
   * @TechDoc("Get the doctor first name of the doctor.")
   *
   * @return null|string
   *   The first name of the entity, or NULL if there is no first name defined
   */
  public function firstName();

  /**
   * @TechDoc("Get the doctor last name of the doctor.")
   *
   * @return null|string
   *   The last name of the entity, or NULL if there is no last name defined
   */
  public function lastName();

  /**
   * @TechDoc("Get the Schedule Appointment of the doctor.")
   *
   * @return null|bool
   *   The Schedule Appointment of the entity, or NULL if there is no SA
   */
  public function scheduleAppointment();

  /**
   * @TechDoc("Get the qualification of the doctor.")
   *
   * @return null|string
   *   The qualification of the entity, or NULL if there is no qualification
   */
  public function qualification();

  /**
   * @TechDoc("Get the practice name of the doctor.")
   *
   * @return null|string
   *   The practice name of the entity, or NULL if there is no practice defined
   */
  public function practice();

  /**
   * @TechDoc("Get the website of the doctor.")
   *
   * @return null|string
   *   The website of the entity, or NULL if there is no website defined
   */
  public function website();

  /**
   * @TechDoc("Get the first address of the doctor.")
   *
   * @return null|string
   *   The address of the entity, or NULL if there is no address defined
   */
  public function address1();

  /**
   * @TechDoc("Get the second address of the doctor.")
   *
   * @return null|string
   *   The address of the entity, or NULL if there is no address defined
   */
  public function address2();

  /**
   * @TechDoc("Get the city of the doctor.")
   *
   * @return null|string
   *   The city of the entity, or NULL if there is no city defined
   */
  public function city();

  /**
   * @TechDoc("Get the area of the doctor.")
   *
   * @return null|string
   *   The area of the entity, or NULL if there is no area defined
   */
  public function area();

  /**
   * @TechDoc("Get the country of the doctor.")
   *
   * @return null|string
   *   The country of the entity, or NULL if there is no country defined
   */
  public function country();

  /**
   * @TechDoc("Get the phone of the doctor.")
   *
   * @return null|string
   *   The phone of the entity, or NULL if there is no phone defined
   */
  public function phone();

  /**
   * @TechDoc("Get the email of the doctor.")
   *
   * @return null|string
   *   The email of the entity, or NULL if there is no email defined
   */
  public function email();

  /**
   * @TechDoc("Get the zipcode of the doctor.")
   *
   * @return null|string
   *   The zipcode of the entity, or NULL if there is no zipcode defined
   */
  public function getZipcode();

  /**
   * @TechDoc("Get the customer id of the doctor.")
   *
   * @return null|string
   *   The customer id of the entity, or NULL if there is no customer id defined
   */
  public function customerId();

  /**
   * @TechDoc("Get the provider of the doctor.")
   *
   * @return null|string
   *   The provider of the entity, or NULL if there is no provider defined
   */
  public function isFullData();

  /**
   * @TechDoc("Get the allowed doctor qualification values.")
   *
   * @return array
   *   The array of the qualification values
   */
  public static function xMapLocatorAllowedValues();

}
