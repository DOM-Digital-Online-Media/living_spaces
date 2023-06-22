<?php

namespace Drupal\living_spaces_subgroup\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\living_spaces_subgroup\LivingSpacesSubgroupManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'Group Tree' block.
 *
 * @Block(
 *   id = "living_spaces_group_tree_block",
 *   admin_label = @Translation("Group Tree"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "group" = @ContextDefinition("entity:group", required = TRUE, label = @Translation("Group"))
 *   }
 * )
 */
class LivingSpacesGroupTreeBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the living_spaces_subgroup.manager service.
   *
   * @var \Drupal\living_spaces_subgroup\LivingSpacesSubgroupManagerInterface
   */
  protected $subGroupManager;

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a LivingSpacesGroupTreeBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\living_spaces_subgroup\LivingSpacesSubgroupManagerInterface $sub_group_manager
   *   Interface for living spaces sub-groups manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Interface for classes that manage a set of enabled modules.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LivingSpacesSubgroupManagerInterface $sub_group_manager, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->subGroupManager = $sub_group_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('living_spaces_subgroup.manager'),
      $container->get('entity_type.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if ($group = $this->getContextValue('group')) {
      if ($parent = $this->subGroupManager->getGroupsParent($group)) {
        $build['parent'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $this->t('Space parent: @parent', ['@parent' => $parent->toLink()->toString()]),
          '#attributes' => [
            'class' => ['parent'],
          ],
        ];
      }

      $build['space'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('Space: <b>@space</b>', ['@space' => $group->label()]),
        '#attributes' => [
          'class' => ['space'],
        ],
      ];

      if ($children = $this->subGroupManager->getGroupsChildren($group)) {
        $build['children'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $this->t('Space children:'),
          '#attributes' => [
            'class' => ['children'],
          ],
        ];

        foreach ($this->entityTypeManager->getStorage('group')->loadMultiple($children) as $child) {
          $build['children'][] = [
            '#theme' => 'dropdown',
            '#id' => "space-actions-{$group->id()}-{$child->id()}",
            '#button_class' => 'btn-sm',
            '#button' => $child->label(),
            '#links' => $this->moduleHandler->invokeAll('living_spaces_subgroup_child_actions_info', [$child]),
          ];
        }
      }
    }

    return $build;
  }

}
