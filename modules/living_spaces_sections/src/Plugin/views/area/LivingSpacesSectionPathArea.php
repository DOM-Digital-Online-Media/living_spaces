<?php

namespace Drupal\living_spaces_sections\Plugin\views\area;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface;
use Drupal\views\Plugin\views\area\AreaPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Views area section path handler.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("section_path")
 */
class LivingSpacesSectionPathArea extends AreaPluginBase {

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
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the current_route_match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Constructs a LivingSpacesSectionPathArea object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface $section_manager
   *   Interface for section manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   Provides an interface for an entity type bundle info.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   Default object for current_route_match service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LivingSpacesSectionsManagerInterface $section_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityTypeManagerInterface $entity_type_manager, CurrentRouteMatch $current_route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->sectionManager = $section_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentRouteMatch = $current_route_match;
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
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['bundle'] = ['default' => ''];
    $options['title'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

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
      '#default_value' => $this->options['bundle'],
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->options['title'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    if (!$empty || !empty($this->options['empty'])) {
      if (!empty($this->options['bundle']) && !empty($this->options['title'])) {
        $args = $this->view->args;

        $gid = '';
        if ($parameter = $this->currentRouteMatch->getRawParameter('group')) {
          $gid = $parameter;
        }
        elseif (!empty($args[0])) {
          $gid = $args[0];
        }

        if (is_numeric($gid) && $space = $this->entityTypeManager->getStorage('group')->load($gid)) {
          if ($section = $this->sectionManager->getSectionFromGroupByType($space, $this->options['bundle'])) {
            // phpcs:ignore
            return $section->toLink($this->t($this->options['title']))->toRenderable();
          }
        }
      }
    }

    return [];
  }

}
