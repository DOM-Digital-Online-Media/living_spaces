<?php

namespace Drupal\living_spaces_protected_area\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeRepository;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Form handler for adding protected area settings.
 */
class LivingSpacesProtectedAreaSettingsForm extends ConfigFormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the entity_type.repository service.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepository
   */
  protected $entityTypeRepository;

  /**
   * Returns the entity_type.bundle.info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfo
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs a LivingSpacesProtectedAreaSettingsForm form.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Entity\EntityTypeRepository $entity_type_repository
   *   Provides helper methods for loading entity types.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfo $entity_type_bundle_info
   *   Provides discovery and retrieval of entity type bundles.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, EntityTypeRepository $entity_type_repository, EntityTypeBundleInfo $entity_type_bundle_info) {
    parent::__construct($config_factory);

    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeRepository = $entity_type_repository;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.repository'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_protected_area_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'living_spaces_protected_area.protected_area_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('living_spaces_protected_area.protected_area_settings');

    $entity_type_manager = $this->entityTypeManager;
    $filter = function (string $entity_type_id) use ($entity_type_manager): bool {
      return $entity_type_manager->getDefinition($entity_type_id)->hasKey('id');
    };

    $options = [];
    $types = $this->entityTypeRepository->getEntityTypeLabels(TRUE);
    $content = $this->t('Content', [], ['context' => 'Entity type group']);
    foreach ($types as $group_name => $group) {
      if ($group_name == $content) {
        $options = array_filter($group, $filter, ARRAY_FILTER_USE_KEY);
      }
    }

    $form['types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Entity types'),
      '#options' => $options,
      '#default_value' => !empty($config->get('types')) ? $config->get('types') : [],
    ];

    foreach ($options as $type => $label) {
      $bundles = [];
      foreach ($this->entityTypeBundleInfo->getBundleInfo($type) as $bundle => $info) {
        $bundles[$bundle] = $info['label'];
      }

      $form["{$type}_bundles"] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('@type bundles', ['@type' => $label]),
        '#options' => $bundles,
        '#default_value' => !empty($config->get("{$type}_bundles")) ? $config->get("{$type}_bundles") : [],
        '#states' => [
          'visible' => [
            ":input[name='types[{$type}]']" => ['checked' => TRUE],
          ],
        ],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $bundles = [];
    foreach ($form_state->getValue('types') as $key => $type) {
      if ($type && $options = $form_state->getValue("{$type}_bundles")) {
        $bundles["{$type}_bundles"] = $options;
      }
    }

    $config = $this->configFactory->getEditable('living_spaces_protected_area.protected_area_settings');
    $config->set('types', $form_state->getValue('types'));
    foreach ($bundles as $key => $bundle) {
      $config->set($key, $bundle);
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
