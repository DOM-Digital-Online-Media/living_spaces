<?php

namespace Drupal\living_spaces_intranet\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\living_spaces_intranet\LivingSpacesBansManagerInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the Banned user status constraint.
 */
class LivingSpacesIntranetBannedUserStatusConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The Living Spaces Bans Manager.
   *
   * @var \Drupal\living_spaces_intranet\LivingSpacesBansManagerInterface
   */
  protected LivingSpacesBansManagerInterface $livingSpacesBansManager;

  /**
   * Constructs the object.
   *
   * @param \Drupal\living_spaces_intranet\LivingSpacesBansManagerInterface $livingSpacesBansManager
   *   The Living Spaces Bans Manager.
   */
  public function __construct(LivingSpacesBansManagerInterface $livingSpacesBansManager) {
    $this->livingSpacesBansManager = $livingSpacesBansManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('living_spaces_bans.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
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
