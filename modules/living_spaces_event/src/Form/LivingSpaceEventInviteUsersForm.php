<?php

namespace Drupal\living_spaces_event\Form;

use Drupal\Core\Entity\Element\EntityAutocomplete;
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
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->mailManager = $container->get('plugin.manager.mail');
    $instance->configFactory = $container->get('config.factory');
    return $instance;
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
      if (!living_spaces_event_check_user_status($event->id(), $match)) {
        $event->set('invited_users', $match);

        $message = $this->entityTypeManager->getStorage('message')->create([
          'template' => 'user_invited_to_the_event',
          'uid' => $match,
          'field_event' => $event->id(),
        ]);
        $message->save();

        $config_settings = $this->configFactory->getEditable('user.settings');
        if ($config_settings->get('notify.email_invited_user_to_the_event')) {
          $account = $this->entityTypeManager->getStorage('user')->load($match);
          if ($account->isActive() && $account->getEmail()) {
            $params['event'] = $event;
            $this->mailManager->mail('living_spaces_event', 'email_invited_user_to_the_event', $account->getEmail(), $account->getPreferredLangcode(), $params);
          }
        }
        $event->save();

        $this->messenger()->addStatus($this->t('User has been invited.'));
      }
      else {
        $this->messenger()->addWarning($this->t('User is already invited.'));
      }
    }
    else {
      $this->messenger()->addWarning($this->t('There are no matches.'));
    }
  }

}
