<?php

namespace Drupal\x_map_locator\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @TechDoc("Form handler for the Doctor type validate, add and edit forms.")
 */
class XMapLocatorDoctorDefaultForm extends ContentEntityForm implements ContentEntityFormInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->configFactory = $container->get('config.factory');
    $instance->httpClient = $container->get('http_client');
    $instance->locatorManager = $container->get('x_map_locator.locator_manager');

    return $instance;
  }

  /**
   * TechDoc("Doctor type build form.").
   *
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['location_latitude']['#access'] = FALSE;
    $form['location_longitude']['#access'] = FALSE;

    return $form;
  }

  /**
   * TechDoc("Doctor type form validate method.").
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $values = $form_state->getValues();
    if (!empty($values)
      && isset($values['location_latitude'], $values['location_longitude'])
    ) {
      if (!empty($address1 = $values['address1'][0]['value'])) {
        $address2 = NULL;
        if (!empty($values['address2'][0]['value'])) {
          $address2 = $values['address2'][0]['value'];
        }
        $address = $this->locatorManager->getFullAddress($address1, $address2);
        $addressValues = [
          'city',
          'area',
          'zip',
          'country',
        ];
        if ($address) {
          $additionalValues = [];
          foreach ($addressValues as $value) {
            $item = $values[$value][0]['value'];
            if (!empty($item)) {
              $additionalValues[$value] = $item;
            }
          }
          $location = $this->locatorManager->getLocationByAddress($address, $additionalValues);
        }
      }
      if (!empty($location)
        && !empty($lat = $location['lat'])
        && !empty($long = $location['lng'])
      ) {
        $form_state->setValue('location_latitude', [0 => ['value' => $lat]]);
        $form_state->setValue('location_longitude', [0 => ['value' => $long]]);
      }
      else {
        $form_state->setErrorByName('address1', $this->t('No valid address'));
      }
    }
  }

  /**
   * TechDoc("Doctor type form save method.").
   *
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $entity = $this->entity;
    $entityType = $entity->getEntityType();
    $arguments = [
      '@entity_type' => $entityType->getSingularLabel(),
      '%entity' => $entity->label(),
      'link' => Link::FromTextandUrl($this->t('View list'), Url::fromRoute('view.doctors_list.page_1'))->toString(),
    ];
    $this->logger($entity->getEntityTypeId())->notice($this->t('The @entity_type %entity has been saved.'), $arguments);
    $this->messenger()->addStatus($this->t('The @entity_type %entity has been saved.', $arguments));
    $url = Url::fromRoute('view.doctors_list.page_1');
    $form_state->setRedirectUrl($url);
  }

}
