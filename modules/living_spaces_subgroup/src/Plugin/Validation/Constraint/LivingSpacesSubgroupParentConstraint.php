<?php

namespace Drupal\living_spaces_subgroup\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Plugin implementation of the 'living_space_subgroup_parent_constraint'.
 *
 * @Constraint(
 *   id = "LivingSpacesSubgroupParentConstraint",
 *   label = @Translation("Parent field constraint", context = "Validation"),
 * )
 */
class LivingSpacesSubgroupParentConstraint extends Constraint {

  /**
   * The message that will be shown if parent is equal to the group.
   *
   * @var string
   */
  public $parentEqualGroup = 'Parent cannot be equal to the group. Choose another parent.';

  /**
   * The message that will be shown if circular reference detected.
   *
   * @var string
   */
  public $circularReference = 'Parent %value set to the group will create a circular reference. Choose another parent.';

}
