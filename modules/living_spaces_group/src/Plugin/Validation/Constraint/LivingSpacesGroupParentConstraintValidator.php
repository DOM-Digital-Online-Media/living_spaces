<?php

namespace Drupal\living_spaces_group\Plugin\Validation\Constraint;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the LivingSpacesGroupParentConstraint constraint.
 */
class LivingSpacesGroupParentConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Returns the module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Returns the database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new LivingSpacesGroupParentConstraintValidator.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Interface for classes that manage a set of enabled modules.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   */
  public function __construct(ModuleHandlerInterface $module_handler, Connection $database) {
    $this->moduleHandler = $module_handler;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if ($object = $this->context->getObject()) {
      $bundle = $object->getEntity()->bundle();

      $values = [];
      /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $item */
      foreach ($items as $item) {
        if ($value = $item->getValue()) {
          $values[] = $value['target_id'];
        }
      }

      $info = $this->moduleHandler->invokeAll('living_spaces_group_type_info');
      if ($bundle && isset($info[$bundle])) {
        if (empty($values)) {
          $this->context->addViolation($constraint->require);
        }
        elseif (!empty($info[$bundle]['parent_types']) && is_array($info[$bundle]['parent_types'])) {
          foreach ($values as $value) {
            $query = $this->database->select('groups_field_data', 'gfd');
            $query->fields('gfd', ['type']);
            $query->condition('gfd.id', $value);

            if ($result = $query->execute()->fetchAssoc()) {
              if (!in_array($result['type'], $info[$bundle]['parent_types'])) {
                $this->context->addViolation($constraint->groupType);
                break;
              }
            }
          }
        }
      }
    }
  }

}
