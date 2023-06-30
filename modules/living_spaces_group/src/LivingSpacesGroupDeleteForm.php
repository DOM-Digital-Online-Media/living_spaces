<?php

namespace Drupal\living_spaces_group;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a generic base class for a content entity deletion form.
 */
class LivingSpacesGroupDeleteForm extends ContentEntityDeleteForm {

  /**
   * Returns the module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a LivingSpacesGroupDeleteForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Interface for classes that manage a set of enabled modules.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, ModuleHandlerInterface $module_handler) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);

    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $form_state->getFormObject()->getEntity();

    $items = [];
    foreach ($group->getRelationships() as $relationship) {
      $items['group_relationship'][] = $relationship->id();
    }

    $items['group'][] = $group->id();
    $this->moduleHandler->alter('living_spaces_group_remove_group_content', $group, $items);

    $operations = [];
    foreach ($items as $type => $ids) {
      foreach ($ids as $id) {
        $operations[] = [
          'living_spaces_group_remove_referenced_entity',
          [$type, $id],
        ];
      }
    }

    $batch = [
      'operations' => $operations,
    ];
    batch_set($batch);

    $form_state->setRedirectUrl($this->getRedirectUrl());
    $this->messenger()->addStatus($this->getDeletionMessage());
    $this->logDeletionMessage();
  }

}
