<?php

namespace Drupal\living_spaces_users\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted to create user does not have email.
 *
 * @Constraint(
 *   id = "UserEmailEmpty",
 *   label = @Translation("User Email Is Empty", context = "Validation"),
 *   type = "string"
 * )
 */
class UserEmailEmptyConstraint extends Constraint {
  public $creatorHasNoEmail = 'The creator does not have email, the user email is required.';
}
