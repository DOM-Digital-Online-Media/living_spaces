<?php

namespace Drupal\living_spaces_default\Access;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\Routing\Route;
use Drupal\group\Access\GroupRelationshipCreateAnyEntityAccessCheck;

/**
 * LivingSpacesDefaultCreatePageAccess class.
 */
class LivingSpacesDefaultCreatePageAccess extends GroupRelationshipCreateAnyEntityAccessCheck {

  /**
   * Check access for default content.
   */
  public function access(Route $route, AccountInterface $account, GroupInterface $group) {
    if ($group->get('is_default')->getString()) {
      return AccessResult::forbidden();
    }

    $route->setRequirement('_group_relationship_create_any_entity_access', 'TRUE');
    return parent::access($route, $account, $group);
  }

}
