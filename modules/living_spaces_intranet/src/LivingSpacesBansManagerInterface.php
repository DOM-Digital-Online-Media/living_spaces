<?php

namespace Drupal\living_spaces_intranet;

use Drupal\Core\Session\AccountInterface;

/**
 * Interface for ban manager service.
 */
interface LivingSpacesBansManagerInterface {

  /**
   * Returns a list of user bans.
   *
   * @param AccountInterface $user
   *   User entity.
   * @param array $types
   *   An array of ban bundles.
   *
   * @return array
   *   An array of user bans.
   */
  public function getUserBans(AccountInterface $user, array $types = []): array;

  /**
   * Adds user ban.
   *
   * @param AccountInterface $user
   *   User entity.
   * @param array $data
   *   Array of values.
   */
  public function setUserBan(AccountInterface $user, array $data);

}
