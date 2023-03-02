<?php

namespace Drupal\living_spaces_event\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\living_spaces_event\Entity\LivingSpaceEventInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;

/**
 * Form handler for inviting users.
 */
class LivingSpaceEventInviteUsersForm extends FormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a LivingSpaceEventInviteUsersForm form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_event_invite_users_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, LivingSpaceEventInterface $event = NULL) {
    if (!$event) {
      return [];
    }

    $form['user'] = [
      '#type' => 'autocomplete_deluxe',
      '#title' => $this->t('User name'),
      '#multiple' => TRUE,
      '#target_type' => 'user',
      '#weight' => 2,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#weight' => 3,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $info = $form_state->getBuildInfo();
    $values = $form_state->getValues();

    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $info['args'][0];

    if (!empty($values['user'])) {
      $manager = $this->entityTypeManager->getStorage('user');
      foreach ($values['user'] as $value) {
        $group->addMember($manager->load($value['target_id']));
      }
    }

    $this->messenger()->addStatus($this->t('Group membership has been saved.'));
  }

}
