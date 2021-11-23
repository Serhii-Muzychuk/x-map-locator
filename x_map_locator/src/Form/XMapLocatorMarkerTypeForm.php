<?php

namespace Drupal\x_map_locator\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @TechDoc("Form handler for the Marker Type add and edit forms.")
 */
class XMapLocatorMarkerTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->markerStorage = $container
      ->get('entity_type.manager')
      ->getStorage('x_map_locator_marker_type');

    return $instance;
  }

  /**
   * @TechDoc("Marker types form build.")
   *
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $markerType = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 255,
      '#default_value' => $markerType->label(),
      '#description' => $this->t('Title for the Marker Type.'),
      '#required' => TRUE,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#rows' => 5,
      '#default_value' => $markerType->getDescription(),
      '#description' => $this->t('Description for the Marker Type.'),
      '#required' => TRUE,
    ];
    $form['icon'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Pin icon'),
      '#default_value' => $markerType->getIconForForm(),
      '#description' => $this->t('Icon for pin the Marker Type.'),
      '#upload_location' => 'public://x-locator-icon/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png gif jpg jpeg svg'],
      ],
      '#accept' => '.png, .gif, .jpg, .jpeg, .svg',
      '#required' => TRUE,
    ];
    $form['internal_icon'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Pin internal image'),
      '#default_value' => $markerType->getInternalIconForForm(),
      '#description' => $this->t('Internal image for pin the Marker Type.'),
      '#upload_location' => 'public://x-locator-icon/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png gif jpg jpeg svg'],
      ],
      '#accept' => '.png, .gif, .jpg, .jpeg, .svg',
      '#required' => TRUE,
    ];
    $form['weight'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Weight'),
      '#default_value' => $markerType->getWeight(),
      '#description' => $this->t('Weight for the Marker Type.'),
      '#required' => FALSE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $markerType->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
      '#disabled' => !$markerType->isNew(),
    ];

    return $form;
  }

  /**
   * @TechDoc("Set marker type values to entity.")
   *
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $markerType = $this->entity;
    $iconValue = $form_state->getValue('icon');
    $internalIconValue = $form_state->getValue('internal_icon');
    $iconId = $this->iconSavePermanent($iconValue);
    $internalIconId = $this->iconSavePermanent($internalIconValue);

    $markerType->set('weight', $form_state->getValue('weight'));
    $markerType->set('icon', $iconId);
    $markerType->set('internal_icon', $internalIconId);
    $markerType->set('description', $form_state->getValue('description'));
    $status = $markerType->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('The %label created.', [
        '%label' => $markerType->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label updated.', [
        '%label' => $markerType->label(),
      ]));
    }
    $form_state->setRedirect('entity.x_map_locator_marker_type.collection');
  }

  /**
   * @TechDoc("Helper function to check whether an Marker Type configuration entity exists.")
   *
   * @param mixed $id
   */
  public function exist($id) {
    $entity = $this->markerStorage->getQuery()
      ->condition('id', $id)
      ->execute();

    return (bool) $entity;
  }

  /**
   * @TechDoc("Helper function to save permanent icons.")
   *
   * @param $iconValue
   *   The icon value
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @return null|int|mixed|string
   */
  public function iconSavePermanent($iconValue) {
    if (empty($iconValue)) {
      return '';
    }

    $id = '';
    if (isset($iconValue[0])) {
      $file = $this->entityTypeManager->getStorage('file')->load($iconValue[0]);
      if (!empty($file)) {
        $file->setPermanent();
        $file->save();
        $id = $file->id();
      }
    }

    return $id;
  }

}
