<?php

namespace Drupal\x_map_locator;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * @TechDoc("Access controller for the XMapLocatorDoctor entity.")
 *
 * @see \Drupal\x_map_locator\Entity\XMapLocatorDoctor.
 */
class XMapLocatorDoctorAccessControlHandler extends EntityAccessControlHandler {

  /**
   * @TechDoc("Provides access check for doctors managing.")
   *
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view x_map_locator_doctor entity');

      case 'edit':
        return AccessResult::allowedIfHasPermission($account, 'edit x_map_locator_doctor entity');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete x_map_locator_doctor entity');
    }

    return AccessResult::allowed();
  }

  /**
   * @TechDoc("Provides access check for doctors creating.")
   *
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add x_map_locator_doctor entity');
  }

}
