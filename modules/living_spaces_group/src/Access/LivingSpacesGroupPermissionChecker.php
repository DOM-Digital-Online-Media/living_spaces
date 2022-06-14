<?php

namespace Drupal\living_spaces_group\Access;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\Access\GroupPermissionCheckerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\living_spaces_group\LivingSpacesGroupManagerInterface;

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
   * Decorator constructor.
   */
  public function __construct(GroupPermissionCheckerInterface $original, LivingSpacesGroupManagerInterface $living_spaces_manager) {
    $this->originalService = $original;
    $this->livingSpacesMananger = $living_spaces_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPermissionInGroup($permission, AccountInterface $account, GroupInterface $group) {
    if ($this->livingSpacesMananger->isLivingSpace($group->bundle())) {
      if (in_array('office_manager', $account->getRoles())) {
        return TRUE;
      }
    }

    return $this->originalService->hasPermissionInGroup($permission, $account, $group);
  }

}
