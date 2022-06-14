<?php

namespace Drupal\living_spaces_group_privacy\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks the submitted value.
 *
 * @Constraint(
 *   id = "LivingSpacesGroupPrivacy",
 *   label = @Translation("Space Privacy", context = "Validation"),
 *   type = "list_string"
 * )
 */
class LivingSpacesGroupPrivacy extends Constraint {

  /**
   * Provides 'require' error message.
   *
   * @var string
   */
  public $require = 'This field is required';

}
