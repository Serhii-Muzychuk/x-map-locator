<?php

namespace Drupal\x_map_locator\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\x_map_locator\XMapLocatorDoctorInterface;

/**
 * @TechDoc("Defines the XMapLocatorDoctor entity.")
 *
 * @ingroup XMapLocator
 *
 * @ContentEntityType(
 *   id = "x_map_locator_doctor",
 *   label = @Translation("X Map Locator Doctor entity"),
 *   handlers = {
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\x_map_locator\XMapLocatorDoctorAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\x_map_locator\Form\XMapLocatorDoctorDefaultForm",
 *       "edit" = "Drupal\x_map_locator\Form\XMapLocatorDoctorDefaultForm",
 *       "delete" = "Drupal\x_map_locator\Form\XMapLocatorDoctorDeleteForm",
 *     },
 *     "list_builder" = "Drupal\x_map_locator\XMapLocatorListBuilder",
 *   },
 *   base_table = "x_map_locator_doctor",
 *   admin_permission = "administer x_map_locator_doctor entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "created" = "created",
 *     "changed" = "changed",
 *   },
 *   links = {
 *     "collection" = "/admin/x/x-map-locator/doctor/list",
 *     "add-form" = "/admin/x/x-map-locator/doctor/add",
 *     "edit-form" = "/admin/x/x-map-locator/doctor/{x_map_locator_doctor}/edit",
 *     "delete-form" = "/admin/x/x-map-locator/doctor/{x_map_locator_doctor}/delete",
 *   },
 * )
 */
class XMapLocatorDoctor extends ContentEntityBase implements XMapLocatorDoctorInterface {
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function isFullData() {
    return !empty($this->get('is_full_data')->value) ? $this->get('is_full_data')->value : '';
  }

  /**
   * {@inheritdoc}
   */
  public function firstName() {
    return $this->get('first_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function lastName() {
    return $this->get('last_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function practice() {
    return $this->get('practice')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function website() {
    return $this->get('website')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function scheduleAppointment() {
    return $this->get('schedule_appointment')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabelAndIcon() {
    $entityId = $this->get('qualification')->value;
    if (!empty($entityId)) {
      $entity = $this->entityTypeManager()
        ->getStorage('x_map_locator_marker_type')
        ->load($entityId);

      return [
        'label' => $entity->getLabel() ?? '',
        'icon_img_render' => $entity->getIconRealUrl() ?? '',
        'internal_icon_img_render' => $entity->getInternalIconRealUrl() ?? '',
      ];
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function qualification() {
    return $this->get('qualification')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconUrl() {
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return $this->firstName() . ' ' . $this->lastName();
  }

  /**
   * {@inheritdoc}
   */
  public function getAddress() {
    $address = $this->address1();
    if (!empty($address2 = $this->address2())) {
      $address .= ' ' . $address2;
    }

    return $address;
  }

  /**
   * {@inheritdoc}
   */
  public function address1() {
    $address1 = NULL;
    if ($this->hasField('address1') && !$this->get('address1')->isEmpty()) {
      $address1 = $this->get('address1')->value;
    }

    return $address1;
  }

  /**
   * {@inheritdoc}
   */
  public function address2() {
    return $this->get('address2')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function city() {
    return $this->get('city')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function phone() {
    return $this->get('phone')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function area() {
    return $this->get('area')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function country() {
    return $this->get('country')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function email() {
    return $this->get('email')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getZipcode() {
    return $this->get('zip')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function customerId() {
    return $this->get('customer_id')->value;
  }

  /**
   * @TechDoc("Define the field properties.")
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *
   * @return array
   *   The array with fields
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entityType): array {
    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Doctor entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Doctor entity.'))
      ->setReadOnly(TRUE);

    $fields['is_full_data'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Is full data'))
      ->setDescription(t('All field success'))
      ->setDefaultValue(TRUE)
      ->setReadOnly(TRUE);

    $fields['name_prefix'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name Prefix'))
      ->setDescription(t('Provides prefix before First Name.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // First name field for the doctor.
    $fields['first_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('First name'))
      ->setDescription(t('Doctor first name.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Last name field for the doctor.
    $fields['last_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Last name'))
      ->setDescription(t('Doctor last name.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Type field for the doctor.
    $fields['qualification'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Doctor qualification'))
      ->setDescription(t('Qualification doctor'))
      ->setDefaultValue('')
      ->setSettings([
        'allowed_values_function' => 'Drupal\x_map_locator\Entity\XMapLocatorDoctor::xMapLocatorAllowedValues',
      ])
      ->setDisplayOptions('view', [
        'label' => 'visible',
        'type' => 'list_default',
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Practice field for the doctor.
    $fields['practice'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Practice Name'))
      ->setDescription(t('Practice name.'))
      ->setRequired(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Website field for the doctor.
    $fields['website'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Website'))
      ->setDescription(t('Website of the doctor.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Schedule Appointment field for the doctor.
    $fields['schedule_appointment'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Schedule Appointment'))
      ->setDescription(t('Schedule Appointment of the doctor.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 7,
      ])
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Status.
    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('Doctor status(active on the map/deactivated on the map).'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 8,
      ])
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 8,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // First address field for the doctor.
    $fields['address1'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Address 1'))
      ->setDescription(t('First doctor address'))
      ->setRequired(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 9,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 9,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Second address field for the doctor.
    $fields['address2'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Address 2'))
      ->setDescription(t('Second doctor address'))
      ->setSettings([
        'default_value' => ' ',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 10,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // City field of the doctor.
    $fields['city'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Doctor city'))
      ->setDescription(t('The city of the Doctor.'))
      ->setRequired(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 11,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 11,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // The zipcode of the Doctor
    $fields['zip'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Doctor zipcode'))
      ->setDescription(t('The zipcode of the Doctor.'))
      ->setRequired(TRUE)
      ->setSettings([
        'default_value' => '',
        'min_length' => 5,
        'max_length' => 8,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 12,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 12,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // The area of the Doctor
    $fields['area'] = BaseFieldDefinition::create('string')
      ->setLabel(t('State/province/region'))
      ->setDescription(t('The state/province/region of the Doctor.'))
      ->setRequired(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 13,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 13,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // The country of the Doctor
    $fields['country'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Doctor Country'))
      ->setDescription(t('The country of the Doctor.'))
      ->setRequired(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 14,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 14,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Telephone number of the Doctor
    $fields['phone'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Doctor phone number'))
      ->setDescription(t('Telephone number of the Doctor.'))
      ->setRequired(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 15,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Customer ID number of the Doctor
    $fields['customer_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Customer ID number'))
      ->setDescription(t('Customer ID of the Doctor.'))
      ->setRequired(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 16,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 16,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Email of the Doctor
    $fields['email'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Doctor EMail'))
      ->setDescription(t('EMail of the Doctor.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 17,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 17,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['location_latitude'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Latitude'))
      ->setDescription(t('Location latitude.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 18,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 18,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['location_longitude'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Longitude'))
      ->setDescription(t('Location longitude.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 19,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 19,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation, AccountInterface $account = NULL, $returnAsObject = FALSE) {
    if ($operation == 'create') {
      return $this->entityTypeManager()
        ->getAccessControlHandler($this->entityTypeId)
        ->createAccess($this->bundle(), $account, [], $returnAsObject);
    }

    return $this->entityTypeManager()
      ->getAccessControlHandler($this->entityTypeId)
      ->access($this, $operation, $account, $returnAsObject);
  }

  /**
   * {@inheritdoc}
   */
  public static function xMapLocatorAllowedValues(): array {
    $marker_entities = \Drupal::entityTypeManager()
      ->getStorage('x_map_locator_marker_type')
      ->loadMultiple();
    $marker_list = [];
    if (!empty($marker_entities)) {
      foreach ($marker_entities as $marker_entity) {
        $marker_list[$marker_entity->id()] = $marker_entity->label();
      }
      ksort($marker_list);
    }

    return $marker_list;
  }

}
