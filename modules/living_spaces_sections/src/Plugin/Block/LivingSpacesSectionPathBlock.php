<?php

namespace Drupal\living_spaces_sections\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface;

/**
 * Block with 'LivingSpacesSectionPathBlock' form.
 *
 * @Block(
 *   id = "living_spaces_sections_path_block",
 *   admin_label = @Translation("Section path"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "group" = @ContextDefinition("entity:group", required = TRUE, label = @Translation("Group"))
 *   }
 * )
 */
class LivingSpacesSectionPathBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the living_spaces_sections.manager service.
   *
   * @var \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface
   */
  protected $sectionManager;

  /**
   * Returns the entity_type.bundle.info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs a LivingSpacesSectionPathBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface $section_manager
   *   Interface for section manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   Provides an interface for an entity type bundle info.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LivingSpacesSectionsManagerInterface $section_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->sectionManager = $section_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('living_spaces_sections.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'bundle' => '',
      'label' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $options = [];
    foreach ($this->entityTypeBundleInfo->getBundleInfo('living_spaces_section') as $bundle => $info) {
      $options[$bundle] = $info['label'];
    }

    $form['bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Bundle'),
      '#options' => $options,
      '#empty_option' => $this->t(' - Select a value - '),
      '#empty_value' => '',
      '#default_value' => isset($config['bundle']) ? $config['bundle'] : '',
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => isset($config['title']) ? $config['title'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['bundle'] = $form_state->getValue('bundle');
    $this->configuration['title'] = $form_state->getValue('title');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $space = $this->getContextValue('group');

    if (!empty($space->in_preview)) {
      return [];
    }

    if (!empty($this->configuration['title']) &&
      !empty($this->configuration['bundle']) &&
      $section = $this->sectionManager->getSectionFromGroupByType($space, $this->configuration['bundle'])
    ) {
      // phpcs:ignore
      return $section->toLink($this->t($this->configuration['title']))->toRenderable();
    }

    return [];
  }

}
