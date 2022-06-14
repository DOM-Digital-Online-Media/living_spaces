<?php

namespace Drupal\living_spaces_sections\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for a living space section path property.
 *
 * @Constraint(
 *   id = "LivingSpacesSectionsPathConstraint",
 *   label = @Translation("Living Spaces section path constraint", context = "Validation"),
 * )
 */
class LivingSpacesSectionsPathConstraint extends Constraint {

  /**
   * The message that will be shown if the value is empty.
   *
   * @var string
   */
  public $isEmpty = 'Path cannot be empty.';

  /**
   * The message that will be shown if the value is not unique.
   *
   * @var string
   */
  public $notUnique = 'The path %value already exists on the group, choose different one.';

}
