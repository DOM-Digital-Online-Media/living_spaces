<?php

namespace Drupal\living_spaces_protected_area\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Access Grant entity.
 */
class LivingSpacesAccessGrantAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('access protected area manage pages')) {
      return AccessResult::allowed();
    }

    /** @var \Drupal\living_spaces_protected_area\Entity\LivingSpacesProtectedAreaAccessGrantInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          $access_result = AccessResult::allowedIfHasPermissions($account, ['view unpublished access grant']);
        }
        else {
          $access_result = AccessResult::allowedIfHasPermissions($account, ['view access grant']);
        }

        return $access_result->addCacheableDependency($entity);

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ['edit access grant']);

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ['delete access grant']);

    }

    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowed();
  }

}
