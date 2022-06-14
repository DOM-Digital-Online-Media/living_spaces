<?php

namespace Drupal\living_spaces_group\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks the submitted value.
 *
 * @Constraint(
 *   id = "LivingSpacesGroupParentConstraint",
 *   label = @Translation("Living Spaces Parent Constraint", context = "Validation"),
 * )
 */
class LivingSpacesGroupParentConstraint extends Constraint {

  /**
   * The message that will be shown if field is required.
   *
   * @var string
   */
  public $require = 'This field is required';

  /**
   * The message that will be shown if group type is not valid.
   *
   * @var string
   */
  public $groupType = 'The group type is invalid';

}
