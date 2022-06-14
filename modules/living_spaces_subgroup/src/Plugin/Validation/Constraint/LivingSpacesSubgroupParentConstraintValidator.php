<?php

namespace Drupal\living_spaces_subgroup\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\group\Entity\GroupInterface;

/**
 * Validates the UniqueInteger constraint.
 */
class LivingSpacesSubgroupParentConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Subgroups manager service.
   *
   * @var \Drupal\living_spaces_subgroup\LivingSpacesSubgroupManagerInterface
   */
  protected $subgroupsManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = new static();
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->subgroupsManager = $container->get('living_spaces_subgroup.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    /** @var \Drupal\living_spaces_subgroup\Plugin\Validation\Constraint\LivingSpacesSubgroupParentConstraint $constraint */
    foreach ($items as $item) {
      if (empty($item->entity)) {
        continue;
      }

      if ($this->isParentEqualToGroup($item->entity)) {
        $this->context->addViolation($constraint->parentEqualGroup, ['%value' => $item->entity->id()]);
      }

      if ($this->isCircularReference($item->entity)) {
        $this->context->addViolation($constraint->circularReference, ['%value' => $item->entity->id()]);
      }
    }
  }

  /**
   * Check if group used as parent is the same group to which parent is added.
   *
   * @param \Drupal\group\Entity\GroupInterface $parent
   *   Parent group.
   *
   * @return bool
   *   TRUE if groups are the same.
   */
  private function isParentEqualToGroup(GroupInterface $parent) {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $this->context->getObject()->getEntity();
    return $group->id() === $parent->id();
  }

  /**
   * Check if group used as parent to the group creates a circular reference.
   *
   * @param \Drupal\group\Entity\GroupInterface $parent
   *   Parent group.
   *
   * @return bool
   *   TRUE if circular reference detected.
   */
  private function isCircularReference(GroupInterface $parent) {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $this->context->getObject()->getEntity();
    $group_tree = $group->isNew()
      ? []
      : $this->subgroupsManager->getGroupsChildren($group, 0);

    // Circular reference can only appear if parent we want to add is already
    // referenced as children of the group.
    return in_array($parent->id(), $group_tree);
  }

}
