<?php

namespace Drupal\living_spaces_event\Form;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\living_spaces_event\Entity\LivingSpaceEventInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    $form['invite'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Invite'),
      '#autocomplete_route_name' => 'living_spaces_event.invite_autocomplete',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Invite'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $info = $form_state->getBuildInfo();
    $values = $form_state->getValues();

    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInterface $event */
    $event = $info['args'][0];

    if (!empty($values['invite']) && $match = EntityAutocomplete::extractEntityIdFromAutocompleteInput($values['invite'])) {
      if (strpos($values['invite'], '[user]')) {
        if (!$event->get('space')->isEmpty() && !living_spaces_event_check_user_status($event->id(), $match)) {
          /** @var \Drupal\group\Entity\Group $space */
          $space = $event->get('space')->entity;

          $group_content_storage = $this->entityTypeManager->getStorage('group_content');
          if ($group_content_storage->loadByGroup($space, 'group_membership', ['entity_id' => $match])) {
            $event->set('invited_users', $match);
            $event->save();

            $this->messenger()->addStatus($this->t('User has been invited.'));
          }
          else {
            $this->messenger()->addWarning($this->t('User doesn\'t have a membership in this space.'));
          }
        }
        else {
          $this->messenger()->addWarning($this->t('User is already invited.'));
        }
      }
      elseif (strpos($values['invite'], '[space]')) {
        $event->set('invited_spaces', $match);
        $event->save();

        $this->messenger()->addStatus($this->t('Space members have been invited.'));
      }
      else {
        $this->messenger()->addWarning($this->t('There are no matches.'));
      }
    }
    else {
      $this->messenger()->addWarning($this->t('There are no matches.'));
    }
  }

}
