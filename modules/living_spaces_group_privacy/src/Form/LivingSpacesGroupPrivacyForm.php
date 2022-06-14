<?php

namespace Drupal\living_spaces_group_privacy\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Form handler for group privacy.
 */
class LivingSpacesGroupPrivacyForm extends ConfigFormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new LivingSpacesGroupPrivacyForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_group_privacy_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'living_spaces_group_privacy.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('living_spaces_group_privacy.settings');
    $entity_mamanger = $this->entityTypeManager->getStorage('group_type');

    $options = [];
    foreach ($entity_mamanger->loadMultiple() as $key => $type) {
      $options[$key] = $type->label();
    }

    $form['bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Bundles'),
      '#options' => $options,
      '#default_value' => !empty($config->get('bundles')) ? $config->get('bundles') : [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('living_spaces_group_privacy.settings')
      ->set('bundles', $form_state->getValue('bundles'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
