<?php

namespace Drupal\living_spaces_subgroup;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\Access\GroupPermissionCheckerInterface;
use Drupal\group\Entity\GroupInterface;

/**
 * Group permission checker service decorator.
 */
class LivingSpacesSubgroupPermissionChecker implements GroupPermissionCheckerInterface {

  /**
   * Decorated service.
   *
   * @var \Drupal\group\Access\GroupPermissionCheckerInterface
   */
  protected $originalService;

  /**
   * Sub-groups manager service.
   *
   * @var \Drupal\living_spaces_subgroup\LivingSpacesSubgroupManagerInterface
   */
  protected $subgroupManager;

  /**
   * Decorator constructor.
   */
  public function __construct(GroupPermissionCheckerInterface $original, LivingSpacesSubgroupManagerInterface $subgroup_manager) {
    $this->originalService = $original;
    $this->subgroupManager = $subgroup_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPermissionInGroup($permission, AccountInterface $account, GroupInterface $group) {
    $result = $this->originalService->hasPermissionInGroup($permission, $account, $group);

    // If user does not have access in group, but group has parent, check on it.
    if (!$result && ($parent = $this->subgroupManager->getGroupsParent($group))) {
      return $this->hasPermissionInGroup($permission, $account, $parent);
    }

    return $result;
  }

}
