<?php

namespace Drupal\living_spaces_default\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the Create access constraint.
 */
class LivingSpacesDefaultCreateAccessConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint): void {
    $this->context->addViolation($constraint->message);
  }

}
