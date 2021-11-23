<?php

namespace Drupal\x_map_locator\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @TechDoc("Builds the form to delete an MarkerType.")
 */
class XMapLocatorMarkerTypeDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->doctorStorage = $container->get('entity_type.manager')->getStorage('x_map_locator_doctor');

    return $instance;
  }

  /**
   * @TechDoc("Provides the getting delete question message.")
   *
   * {@inheritdoc}
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Are you sure you want to delete %name?', [
      '%name' => $this->entity->label(),
    ]);
  }

  /**
   * @TechDoc("Provides the cancel url.")
   *
   * {@inheritdoc}
   */
  public function getCancelUrl(): Url {
    return new Url('entity.x_map_locator_marker_type.collection');
  }

  /**
   * @TechDoc("Provides the confirm delete text.")
   *
   * {@inheritdoc}
   */
  public function getConfirmText(): TranslatableMarkup {
    return $this->t('Delete');
  }

  /**
   * @TechDoc("Provides the validate form method.")
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $result = $this->doctorStorage
      ->getQuery()
      ->condition('qualification', $this->entity->id())
      ->execute();
    if (count($result) < 1) {
      $this->messenger()
        ->addError($this->t('Entity %label has NOt been delete. Other Entity is reference this entity. After delete reference dependencies', [
          '%label' => $this->entity->label(),
        ]));

      return FALSE;
    }

    return $this->entity;
  }

  /**
   * @TechDoc("Provides the submit method to delete the entity.")
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $this->messenger()
      ->addMessage($this->t('Entity %label has been deleted.', ['%label' => $this->entity->label()]));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
