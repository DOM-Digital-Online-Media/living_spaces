<?php

namespace Drupal\living_spaces_group\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Form handler for changing user role.
 */
class LivingSpacesGroupChangeRoleForm extends FormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the tempstore.private service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Constructs a LivingSpacesGroupChangeRoleForm form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   Creates a PrivateTempStore object for a given collection.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PrivateTempStoreFactory $temp_store_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->tempStoreFactory = $temp_store_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_group_user_role_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $temp = $this->tempStoreFactory
      ->get('living_spaces_group_change_role')
      ->get($this->currentUser()->id());

    $options = [];

    /** @var \Drupal\group\Entity\GroupInterface $group */
    if (!empty($temp['group']) && $group = $this->entityTypeManager->getStorage('group')->load($temp['group'])) {
      $roles = $this->entityTypeManager->getStorage('group_role')->loadByProperties([
        'group_type' => $group->bundle(),
        'internal' => FALSE,
      ]);

      foreach ($roles as $role) {
        $options[$role->id()] = $role->label();
      }
    }

    $form['role'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Roles'),
      '#options' => $options,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $roles = [];
    foreach ($values['role'] as $role) {
      if ($role) {
        $roles[] = ['target_id' => $role];
      }
    }

    $temp = $this->tempStoreFactory
      ->get('living_spaces_group_change_role')
      ->get($this->currentUser()->id());

    /** @var \Drupal\group\Entity\GroupInterface $group */
    if ($group = $this->entityTypeManager->getStorage('group')->load($temp['group'])) {
      /** @var \Drupal\user\Entity\User $user */
      foreach ($temp['users'] as $user) {
        $relationship = $group->getMember($user)->getGroupRelationship();
        $relationship->set('group_roles', $roles)->save();
      }
    }
  }

}
