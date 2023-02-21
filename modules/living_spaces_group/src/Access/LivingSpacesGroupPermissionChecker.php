<?php

namespace Drupal\living_spaces_group\Access;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\Access\GroupPermissionCheckerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\living_spaces_group\LivingSpacesGroupManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Decorates default group permissions service.
 */
class LivingSpacesGroupPermissionChecker implements GroupPermissionCheckerInterface {

  /**
   * Decorated service.
   *
   * @var \Drupal\group\Access\GroupPermissionCheckerInterface
   */
  protected $originalService;

  /**
   * Returns the living_spaces_group.manager service.
   *
   * @var \Drupal\living_spaces_group\LivingSpacesGroupManagerInterface
   */
  protected $livingSpacesMananger;

  /**
   * Returns the module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Decorator constructor.
   */
  public function __construct(GroupPermissionCheckerInterface $original, LivingSpacesGroupManagerInterface $living_spaces_manager, ModuleHandlerInterface $module_handler) {
    $this->originalService = $original;
    $this->livingSpacesMananger = $living_spaces_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPermissionInGroup($permission, AccountInterface $account, GroupInterface $group) {
    if ($this->livingSpacesMananger->isLivingSpace($group->bundle())) {
      $permissions = $this->moduleHandler->invokeAll('living_spaces_group_exclude_permissions');
      $exclude = empty($permissions) || !in_array($permission, $permissions);

      if (in_array('office_manager', $account->getRoles()) && $exclude) {
        return TRUE;
      }
    }

    return $this->originalService->hasPermissionInGroup($permission, $account, $group);
  }

}
