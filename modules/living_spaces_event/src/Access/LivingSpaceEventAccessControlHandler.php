<?php

namespace Drupal\living_spaces_event\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Space Event entity.
 */
class LivingSpaceEventAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('administer living spaces event')) {
      return AccessResult::allowed();
    }

    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          $access_result = AccessResult::allowedIfHasPermissions($account, ["view any unpublished {$entity->bundle()} space event"]);

          if (!$access_result->isAllowed() && $account->isAuthenticated() && $account->id() === $entity->getOwnerId()) {
            $access_result = AccessResult::allowedIfHasPermissions($account, ["view own unpublished {$entity->bundle()} space event"])->cachePerUser();
          }
        }
        else {
          $access_result = AccessResult::allowedIfHasPermissions($account, ["view {$entity->bundle()} space event"]);
        }

        return $access_result->addCacheableDependency($entity);

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ["edit {$entity->bundle()} space event"]);

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ["delete {$entity->bundle()} space event"]);

    }

    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $permissions = [
      'administer living spaces event',
      'create ' . $entity_bundle . ' space event',
    ];
    return AccessResult::allowedIfHasPermissions($account, $permissions, 'OR');
  }

}
