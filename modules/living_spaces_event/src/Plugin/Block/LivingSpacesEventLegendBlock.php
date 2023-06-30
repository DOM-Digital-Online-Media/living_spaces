<?php

namespace Drupal\living_spaces_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Block with 'LivingSpacesEventLegendBlock' form.
 *
 * @Block(
 *   id = "living_spaces_event_legend_block",
 *   admin_label = @Translation("Legend block"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "section" = @ContextDefinition("entity:living_spaces_section", required = TRUE, label = @Translation("Living Spaces section"))
 *   }
 * )
 */
class LivingSpacesEventLegendBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the entity_type.bundle.info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entitytypeBuildInfo;

  /**
   * Returns the entity_field.manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a LivingSpacesEventLegendBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_build_info
   *   Provides an interface for an entity type bundle info.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Provides an interface for an entity field manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeBundleInfoInterface $entity_type_build_info, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entitytypeBuildInfo = $entity_type_build_info;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.bundle.info'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface $section */
    $section = $this->getContextValue('section');
    if (empty($section) || empty($section->getGroup())) {
      return [];
    }

    $types = [];
    foreach ($this->entitytypeBuildInfo->getBundleInfo('living_spaces_event') as $name => $bundle) {
      // @todo Check if plugin id is correct.
      if ($section->getGroup()->getGroupType()->hasPlugin("living_spaces_event:{$name}")) {
        $types[$name]['entity_type'] = 'living_spaces_event';
        $types[$name]['field_name'] = 'field_start_date';
        $types[$name]['bundle'] = $name;
        $types[$name]['label'] = $bundle['label'];
      }
    }

    return [
      '#theme' => 'living_spaces_event_fullcalendar_legend',
      '#types' => $types,
    ];
  }

}
