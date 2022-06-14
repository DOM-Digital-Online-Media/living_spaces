<?php

namespace Drupal\living_spaces_group;

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

}
