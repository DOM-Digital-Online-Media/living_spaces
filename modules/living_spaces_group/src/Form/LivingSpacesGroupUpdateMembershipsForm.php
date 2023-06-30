<?php

namespace Drupal\living_spaces_group\Form;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\living_spaces_group\LivingSpacesGroupManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an update memberships batch form.
 */
class LivingSpacesGroupUpdateMembershipsForm extends FormBase {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Living spaces group manager.
   *
   * @var \Drupal\living_spaces_group\LivingSpacesGroupManagerInterface
   */
  protected $livingSpacesManager;

  /**
   * Form constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\living_spaces_group\LivingSpacesGroupManagerInterface $living_spaces_manager
   *   Interface for group manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LivingSpacesGroupManagerInterface $living_spaces_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->livingSpacesManager = $living_spaces_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('living_spaces_group.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_group_living_spaces_group_update_memberships';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['message'] = [
      '#type' => 'item',
      '#markup' => $this->t('Launches batch process to re-save all space memberships with latest dynamic values.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Launch'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $types = $this->livingSpacesManager->getLivingSpaceGroupTypes();

    $memberships = $this->entityTypeManager
      ->getStorage('group_relationship')
      ->getQuery()
      ->condition('type', 'group_membership')
      ->condition('group_type', $types, 'IN')
      ->execute();

    $batch_builder = (new BatchBuilder())
      ->setTitle(t('Updating space memberships'))
      ->setFinishCallback([__CLASS__, 'finishedCallback'])
      ->setInitMessage(t('Fetching all memberships of spaces'));
    foreach ($memberships as $id) {
      $batch_builder->addOperation([__CLASS__, 'batchProcess'], [$id]);
    }
    batch_set($batch_builder->toArray());
  }

  /**
   * Batch item process operation.
   *
   * @param int $id
   *   Group content id.
   * @param array $context
   *   Batch context.
   */
  public static function batchProcess($id, array &$context) {
    /** @var \Drupal\group\Entity\GroupRelationshipInterface $relationship */
    if ($relationship = \Drupal::entityTypeManager()->getStorage('group_relationship')->load($id)) {
      $relationship->save();
    }
  }

  /**
   * Batch finished callback.
   *
   * @param bool $success
   *   Success of the operation.
   * @param array $results
   *   Array of results for post processing.
   * @param array $operations
   *   Array of operations.
   */
  public static function finishedCallback($success, array $results, array $operations) {
    if ($success) {
      \Drupal::messenger()->addMessage(t('Memberships were successfully re-saved.'));
    }
    else {
      $error_operation = reset($operations);
      \Drupal::messenger()->addError(t('An error occurred while processing @operation with arguments : @args', [
        '@operation' => $error_operation[0],
        '@args' => print_r($error_operation[0], TRUE),
      ]));
    }
  }

}
