<?php

namespace Drupal\living_spaces_group\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks the submitted value.
 *
 * @Constraint(
 *   id = "LivingSpacesGroupPreferredSpaces",
 *   label = @Translation("Preferred Spaces", context = "Validation"),
 *   type = "entity_reference"
 * )
 */
class LivingSpacesGroupPreferredSpaces extends Constraint {

  /**
   * The message that will be shown if group is not unique.
   *
   * @var string
   */
  public $unique = 'You should have one group per type.';

}
