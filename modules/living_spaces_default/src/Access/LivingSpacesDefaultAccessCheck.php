<?php

namespace Drupal\living_spaces_default\Access;

use Drupal\Core\Routing\Access\AccessInterface as CoreAccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\Routing\Route;

/**
 * LivingSpacesDefaultAccessCheck class.
 */
class LivingSpacesDefaultAccessCheck implements CoreAccessInterface {

  /**
   * Check access for default content.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    $group = $route_match->getParameter('group');

    if ($group && $group->get('is_default')->getString()) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
