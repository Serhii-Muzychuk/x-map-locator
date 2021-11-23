<?php

namespace Drupal\x_map_locator\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * @TechDoc("Provides a listing of XMapLocatorMarkerType.")
 */
class XMapLocatorMarkerTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * @TechDoc("Provides build for header table of Markers.")
   *
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['label'] = $this->t('Type title');
    $header['id'] = $this->t('Machine name');
    $header['description'] = $this->t('Description');
    $header['icon'] = $this->t('Pin icon');
    $header['internal_icon'] = $this->t('Pin internal image');
    $header['weight'] = $this->t('Weight');

    return $header + parent::buildHeader();
  }

  /**
   * @TechDoc("Provides build for Marker types.")
   *
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $row = [];
    if (!empty($entity)) {
      $row['label'] = $entity->label();
      $row['id'] = $entity->id();
      $row['description'] = $entity->getDescription();
      $row['icon'] = $entity->getIconImage();
      $row['internal_icon'] = $entity->getInternalIconImage();
      $row['weight'] = $entity->getWeight();
    }

    return $row + parent::buildRow($entity);
  }

}
