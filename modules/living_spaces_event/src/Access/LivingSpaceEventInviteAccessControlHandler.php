<?php

namespace Drupal\living_spaces_event\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Space Event Invite entity.
 */
class LivingSpaceEventInviteAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('administer living spaces event invite')) {
      return AccessResult::allowed();
    }

    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEvent $event */
    $event = $entity->get('event')->entity;

    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInviteInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIf($event->access('view', $account));

      case 'update':
        return AccessResult::allowedIf($entity->getOwnerId() == $account->id());

      case 'delete':
        return AccessResult::allowedIf($event->access('update', $account));

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
