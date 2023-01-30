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
      $exclude_permissions = $this->moduleHandler->invokeAll('living_spaces_group_exclude_permissions');
      $account_roles = $account->getRoles();
      foreach ($exclude_permissions as $exclude_permission => $roles) {
        if ($permission === $exclude_permission) {
          foreach ($roles as $role => $access) {
            if (in_array($role, $account_roles)) {
              return $access;
            }
          }
        }
      }
    }

    return $this->originalService->hasPermissionInGroup($permission, $account, $group);
  }

}
