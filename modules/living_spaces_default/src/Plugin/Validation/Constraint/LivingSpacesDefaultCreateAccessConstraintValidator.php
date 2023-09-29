<?php

namespace Drupal\living_spaces_default\Plugin\Validation\Constraint;

use Drupal\user\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the Banned user status constraint.
 */
class LivingSpacesDefaultCreateAccessConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint): void {
    if (!$value instanceof UserInterface) {
      return;
    }

    $active_bans = $this->livingSpacesBansManager
      ->getUserBans($value, ['global']);
    if (!empty($active_bans) && $value->isActive()) {
      $this->context->addViolation($constraint->message);
    }
  }

}
