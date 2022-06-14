<?php

namespace Drupal\living_spaces_group\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;

/**
 * Form handler for adding memberships.
 */
class LivingSpacesGroupMembershipForm extends FormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the keyvalue service.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValue;

  /**
   * Returns the settings service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Constructs a LivingSpacesGroupMembershipForm form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value
   *   Defines the key/value store factory interface.
   * @param \Drupal\Core\Site\Settings $settings
   *   Read only settings that are initialized with the class.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, KeyValueFactoryInterface $key_value, Settings $settings) {
    $this->entityTypeManager = $entity_type_manager;
    $this->keyValue = $key_value;
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('keyvalue'),
      $container->get('settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_group_membership_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, GroupInterface $group = NULL) {
    if (!$group) {
      return [];
    }

    $target_type = 'user';
    $selection_handler = 'default:user';

    $settings = [
      'include_anonymous' => '',
      'filter' => ['type' => '_none'],
      'target_bundles' => '',
      'sort' => ['field' => '_none', 'direction' => 'ASC'],
      'auto_create' => '',
      'match_operator' => 'CONTAINS',
    ];

    $data = serialize($settings) . $target_type . $selection_handler;
    $hmac = base64_encode(hash_hmac('sha256', $data, $this->settings->get('hash_salt'), TRUE));
    $selection_settings_key = str_replace(['+', '/', '='], ['-', '_', ''], $hmac);

    $key_value_storage = $this->keyValue->get('entity_autocomplete');
    if (!$key_value_storage->has($selection_settings_key)) {
      $key_value_storage->set($selection_settings_key, $settings);
    }

    $url = Url::fromRoute('autocomplete_deluxe.autocomplete', [
      'target_type' => $target_type,
      'selection_handler' => $selection_handler,
      'selection_settings_key' => $selection_settings_key,
    ], [
      'absolute' => TRUE,
    ])->getInternalPath();

    $form['user'] = [
      '#type' => 'autocomplete_deluxe',
      '#title' => $this->t('User name'),
      '#autocomplete_deluxe_path' => $url,
      '#multiple' => TRUE,
      '#target_type' => $target_type,
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
