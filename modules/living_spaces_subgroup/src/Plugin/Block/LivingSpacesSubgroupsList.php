<?php

namespace Drupal\living_spaces_subgroup\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'Sub-Groups list' block with the group's children list.
 *
 * @Block(
 *   id = "living_spaces_subgroups_list",
 *   admin_label = @Translation("Sub groups list"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "group" = @ContextDefinition("entity:group", required = TRUE, label = @Translation("Group"))
 *   }
 * )
 */
class LivingSpacesSubgroupsList extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Sub-group manager.
   *
   * @var \Drupal\living_spaces_subgroup\LivingSpacesSubgroupManagerInterface
   */
  protected $subGroupManager;

  /**
   * Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->subGroupManager = $container->get('living_spaces_subgroup.manager');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if ($group = $this->getContextValue('group')) {
      if ($children = $this->subGroupManager->getGroupsChildren($group)) {
        $build['links'] = [
          '#theme' => 'subgroups_list',
          '#group' => $group,
          '#include_self' => FALSE,
        ];
      }
    }

    return $build;
  }

}
