<?php

namespace Drupal\x_map_locator\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * @TechDoc("Builds the form to delete an X Map Locator Doctor.")
 */
class XMapLocatorDoctorDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * @TechDoc("Provides the getting delete question message.")
   *
   * {@inheritdoc}
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Are you sure you want to delete %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * @TechDoc("Provides the cancel url.")
   *
   * {@inheritdoc}
   */
  public function getCancelUrl(): Url {
    return new Url('view.doctors_list.page_1');
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
   * @TechDoc("Provides the submit method to delete the entity.")
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $entity->delete();
    $this->messenger()->addMessage($this->t('Doctor %label has been deleted.', ['%label' => $this->entity->label()]));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
