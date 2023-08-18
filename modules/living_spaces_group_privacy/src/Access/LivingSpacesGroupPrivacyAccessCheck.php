<?php

namespace Drupal\living_spaces_group_privacy\Access;

use Drupal\Core\Routing\Access\AccessInterface as CoreAccessInterface;
use Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\Routing\Route;

/**
 * LivingSpacesGroupPrivacyAccessCheck class.
 */
class LivingSpacesGroupPrivacyAccessCheck implements CoreAccessInterface {

  /**
   * Returns the plugin.manager.living_spaces_group_privacy service.
   *
   * @var \Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyManager
   */
  protected $privacyManager;

  /**
   * Constructs an LivingSpacesGroupPrivacyAccessCheck object.
   *
   * @param \Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyManager $privacy_manager
   *   Provides a living_spaces_group_privacy plugin manager.
   */
  public function __construct(LivingSpacesGroupPrivacyManager $privacy_manager) {
    $this->privacyManager = $privacy_manager;
  }

  /**
   * Check access for default content.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    $group = $route_match->getParameter('group');
    if ($group && $instance = $this->privacyManager->getPrivacyPlugins($group)) {
      if ($account->hasPermission('manage living spaces')) {
        return AccessResult::allowed();
      }

      return $instance->joinAccess($group, $account)
        ? AccessResult::allowed()
        : AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
