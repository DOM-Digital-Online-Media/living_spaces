<?php

namespace Drupal\living_spaces_menus\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkDefault;

/**
 * Represents a menu link for a Membership.
 */
class MembershipLink extends MenuLinkDefault {

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user'];
  }

}
