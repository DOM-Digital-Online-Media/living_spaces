<?php

namespace Drupal\living_spaces_group\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PreferredSpaces constraint.
 */
class LivingSpacesGroupPreferredSpacesValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new PreferredSpacesValidator object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!isset($items) || $items->isEmpty()) {
      return;
    }

    $bundles = [];
    $entity_type_manager = $this->entityTypeManager->getStorage('group');
    foreach ($items as $item) {
      if (!empty($item->getValue()) && $group = $entity_type_manager->load($item->getValue()['target_id'])) {
        if (in_array($group->bundle(), $bundles)) {
          $this->context->addViolation($constraint->unique);
        }
        else {
          $bundles[] = $group->bundle();
        }
      }
    }
  }

}
