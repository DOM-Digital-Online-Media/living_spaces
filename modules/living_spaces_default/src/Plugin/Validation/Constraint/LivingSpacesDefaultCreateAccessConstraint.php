<?php

namespace Drupal\living_spaces_default\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Provides a Create access constraint.
 *
 * @Constraint(
 *   id = "LivingSpacesDefaultCreateAccess",
 *   label = @Translation("Create access", context = "Validation"),
 * )
 */
class LivingSpacesDefaultCreateAccessConstraint extends Constraint {

  /**
   * The error message for the constraint.
   *
   * @var string
   */
  public string $message = 'You cannot add relationships to default spaces.';

}
