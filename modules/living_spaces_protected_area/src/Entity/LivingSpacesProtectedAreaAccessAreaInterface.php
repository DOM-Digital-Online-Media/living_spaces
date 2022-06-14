<?php

namespace Drupal\living_spaces_protected_area\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides an interface for access area entities.
 */
interface LivingSpacesProtectedAreaAccessAreaInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Gets the access area type entity.
   *
   * @return \Drupal\living_spaces_protected_area\Entity\LivingSpacesProtectedAreaAccessAreaTypeInterface
   *   The access area type entity.
   */
  public function getAccessAreaType();

  /**
   * Gets the access area entity by given path.
   *
   * @param string|null $path
   *   The path to math access area entity.
   *
   * @return \Drupal\living_spaces_protected_area\Entity\LivingSpacesProtectedAreaAccessAreaInterface|null
   *   The access area entity.
   */
  public static function fromRestrictedPath($path = NULL);

  /**
   * Gets the access status for the user.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user entity.
   *
   * @return array|null
   *   The array of the access results for the user. The array contains access status and access entity.
   *   It returns null if there are no related access entities.
   */
  public static function getForUser(AccountInterface $user);

}
