<?php

namespace Drupal\living_spaces_intranet\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Provides a Banned user status constraint.
 *
 * @Constraint(
 *   id = "LivingSpacesIntranetBannedUserStatus",
 *   label = @Translation("Banned user status", context = "Validation"),
 * )
 */
class LivingSpacesIntranetBannedUserStatusConstraint extends Constraint {

  /**
   * The error message for the constraint.
   *
   * @var string
   */
  public string $message = 'The user has active global ban, please delete ban if you want to change user status.';

}
