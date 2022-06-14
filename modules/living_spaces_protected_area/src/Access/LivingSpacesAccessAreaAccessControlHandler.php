<?php

namespace Drupal\living_spaces_protected_area\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the access area entity.
 */
class LivingSpacesAccessAreaAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('access protected area manage pages')) {
      return AccessResult::allowed();
    }

    /** @var \Drupal\living_spaces_protected_area\Entity\LivingSpacesProtectedAreaAccessAreaInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermissions($account, ["view {$entity->bundle()}  access area"]);

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ["edit {$entity->bundle()}  access area"]);

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ["delete {$entity->bundle()}  access area"]);

    }

    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $permissions = [
      'access protected area manage pages',
      'create ' . $entity_bundle . '  access area',
    ];
    return AccessResult::allowedIfHasPermissions($account, $permissions, 'OR');
  }

}
