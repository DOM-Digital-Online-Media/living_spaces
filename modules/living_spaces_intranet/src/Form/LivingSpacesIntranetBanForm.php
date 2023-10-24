<?php

namespace Drupal\living_spaces_intranet\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\living_spaces_intranet\LivingSpacesBansManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Form handler for ban user action.
 */
class LivingSpacesIntranetBanForm extends FormBase {

  /**
   * Returns the living_spaces_bans.manager service.
   *
   * @var \Drupal\living_spaces_intranet\LivingSpacesBansManagerInterface
   */
  protected $banManager;

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
   * Constructs a LivingSpacesIntranetBanForm form.
   *
   * @param \Drupal\living_spaces_intranet\LivingSpacesBansManagerInterface $ban_manager
   *   Interface for ban manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   Creates a PrivateTempStore object for a given collection.
   */
  public function __construct(LivingSpacesBansManagerInterface $ban_manager, EntityTypeManagerInterface $entity_type_manager, PrivateTempStoreFactory $temp_store_factory) {
    $this->banManager = $ban_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->tempStoreFactory = $temp_store_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('living_spaces_bans.manager'),
      $container->get('entity_type.manager'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_intranet_ban_user_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $temp = $this->tempStoreFactory
      ->get('living_spaces_intranet_ban_user')
      ->get($this->currentUser()->id());

    if (empty($temp['users'])) {
      return $form;
    }

    $form['type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Type'),
      '#options' => [
        'ban' => $this->t('Ban'),
        'unban' => $this->t('Unban'),
      ],
      '#required' => TRUE,
      '#default_value' => 'ban',
    ];

    $ban_storage = $this->entityTypeManager->getStorage('living_spaces_ban_type');

    $options = [];
    /** @var \Drupal\living_spaces_intranet\Entity\LivingSpacesBanTypeInterface $ban_type */
    foreach ($ban_storage->loadMultiple() as $ban_type) {
      $options[$ban_type->id()] = $ban_type->label();
    }

    $form['bundle'] = [
      '#type' => 'radios',
      '#title' => $this->t('Type'),
      '#options' => $options,
      '#required' => TRUE,
      '#default_value' => isset($options['global']) ? 'global' : '',
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
    $temp = $this->tempStoreFactory
      ->get('living_spaces_intranet_ban_user')
      ->get($this->currentUser()->id());

    if (!empty($temp['users'])) {
      $unban = 'unban' == $form_state->getValue('type');
      $bundle = $form_state->getValue('bundle');

      /** @var \Drupal\user\UserInterface $user */
      foreach ($temp['users'] as $user) {
        if ($unban) {
          $this->banManager->deleteUserBans($user, [$bundle]);
        }
        else {
          $data = ['bundle' => $bundle];
          $this->banManager->setUserBan($user, $data);
        }
      }

      $this->messenger()->addStatus($this->t('Selected user(s) were @action.', [
        '@action' => $unban ? $this->t('Unbanned') : $this->t('Banned'),
      ]));
    }
  }

}
