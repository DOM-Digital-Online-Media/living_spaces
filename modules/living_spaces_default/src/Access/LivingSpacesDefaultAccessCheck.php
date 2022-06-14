<?php

namespace Drupal\living_spaces_default\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * LivingSpacesDefaultAccessCheck class.
 */
class LivingSpacesDefaultAccessCheck implements AccessInterface {

  /**
   * Check access for default content.
   */
  public function access(RouteMatchInterface $route, AccountInterface $account) {
    $group = $route->getParameter('group');

    if ($group && $group->get('is_default')->getString()) {
      return $account->hasPermission('manage living spaces default content') ? AccessResult::allowed() : AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
