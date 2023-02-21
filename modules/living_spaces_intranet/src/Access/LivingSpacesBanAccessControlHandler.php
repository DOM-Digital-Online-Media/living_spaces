<?php

namespace Drupal\living_spaces_intranet\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Ban entity.
 */
class LivingSpacesBanAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('administer ban')) {
      return AccessResult::allowed();
    }

    /** @var \Drupal\living_spaces_intranet\Entity\LivingSpacesBanInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermissions($account, ["view {$entity->bundle()} ban"]);

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ["edit {$entity->bundle()} ban"]);

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ["delete {$entity->bundle()} ban"]);

    }

    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $permissions = [
      'administer ban',
      'create ' . $entity_bundle . ' ban',
    ];
    return AccessResult::allowedIfHasPermissions($account, $permissions, 'OR');
  }

}
