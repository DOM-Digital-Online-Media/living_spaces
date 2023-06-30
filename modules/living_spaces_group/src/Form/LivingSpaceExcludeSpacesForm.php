<?php

namespace Drupal\living_spaces_group\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Form handler for exclude spaces settings.
 */
class LivingSpaceExcludeSpacesForm extends ConfigFormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new LivingSpaceExcludeSpacesForm object.
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
    return 'living_spaces_exclude_spaces';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['living_spaces_group.exclude_spaces'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('living_spaces_group.exclude_spaces');

    $options = [];
    /** @var \Drupal\group\Entity\GroupTypeInterface $type */
    foreach ($this->entityTypeManager->getStorage('group_type')->loadMultiple() as $type) {
      $options[$type->id()] = $type->label();
    }

    $form['spaces'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Spaces'),
      '#options' => $options,
      '#default_value' => !empty($config->get('spaces')) ? $config->get('spaces') : [],
      '#description' => $this->t('You should clear the cache after changing space configs.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($spaces = $form_state->getValue('spaces')) {
      foreach ($spaces as $key => $value) {
        if (empty($value)) {
          unset($spaces[$key]);
        }
      }
    }

    $this->configFactory->getEditable('living_spaces_group.exclude_spaces')
      ->set('spaces', $spaces)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
