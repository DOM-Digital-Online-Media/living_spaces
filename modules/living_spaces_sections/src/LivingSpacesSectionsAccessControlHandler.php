<?php

namespace Drupal\living_spaces_sections;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Living space section entity.
 *
 * @see \Drupal\living_spaces_sections\Entity\LivingSpacesSection.
 */
class LivingSpacesSectionsAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface $entity */

    $manage_access = LivingSpacesSectionsRouteAccessController::checkManageAccess($entity->getGroup(), $account);
    switch ($operation) {
      case 'view':
        return $manage_access->isAllowed()
          ? $manage_access
          : AccessResult::allowedIf($entity->getGroup()->hasPermission("view {$entity->bundle()} section of a living space", $account));

      default:
        return $manage_access;

    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'administer living spaces sections settings');
  }

}
