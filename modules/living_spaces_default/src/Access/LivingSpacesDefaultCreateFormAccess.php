<?php

namespace Drupal\living_spaces_default\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Access\GroupRelationshipCreateEntityAccessCheck;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\Routing\Route;

/**
 * Access check to prevent creating content in space templates.
 */
class LivingSpacesDefaultCreateFormAccess extends GroupRelationshipCreateEntityAccessCheck {

  /**
   * Check access for default content.
   */
  public function access(Route $route, AccountInterface $account, GroupInterface $group, $plugin_id) {
    if ($group->get('is_default')->getString()) {
      return AccessResult::forbidden();
    }

    $route->setRequirement('_group_relationship_create_entity_access', 'TRUE');
    return parent::access($route, $account, $group, $plugin_id);
  }

}
