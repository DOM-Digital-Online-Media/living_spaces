<?php

namespace Drupal\living_spaces_group;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;

/**
 * Interface for group manager service.
 */
interface LivingSpacesGroupManagerInterface {

  /**
   * Returns whether group type has living space functionality enabled.
   *
   * @param string $group_type
   *   Group entity bundle.
   *
   * @return bool
   *   True if living space enabled for the group bundle.
   */
  public function isLivingSpace(string $group_type);

  /**
   * Returns group types with enabled living space functionality.
   *
   * @return array
   *   An array of group types.
   */
  public function getLivingSpaceGroupTypes();

  /**
   * Returns whether user is group admin.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to load the group role entities for.
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group entity to find the user's role entities in.
   *
   * @return bool
   *   True if living space enabled for the group bundle.
   */
  public function isUserSpaceAdmin(AccountInterface $account, GroupInterface $group);

}
