<?php

namespace Drupal\living_spaces_group\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\Cache;

/**
 * Block with 'LivingSpacesGroupOverviewBlock' block.
 *
 * @Block(
 *   id = "living_spaces_group_overview_block",
 *   admin_label = @Translation("Group overview"),
 *   category = @Translation("Living Spaces")
 * )
 */
class LivingSpacesGroupOverviewBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the current_route_match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $route;

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a LivingSpacesGroupOverviewBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   Provides an interface for classes representing the result of routing.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->route = $route;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    if ($group = $this->route->getParameter('group')) {
      $memberships = $this->entityTypeManager
        ->getStorage('group_relationship')
        ->getQuery()
        ->accessCheck(FALSE)
        ->condition('gid', $group->id())
        ->condition('plugin_id', 'group_membership')
        ->count()
        ->execute();

      $content = $this->entityTypeManager
        ->getStorage('group_relationship')
        ->getQuery()
        ->accessCheck(FALSE)
        ->condition('gid', $group->id())
        ->condition('plugin_id', 'group_membership', '<>')
        ->count()
        ->execute();
      if ($group->hasField('content_sections') && !$group->get('content_sections')->isEmpty()) {
        $content += $group->get('content_sections')->count();
      }

      return [
        '#theme' => 'item_list',
        '#items' => [
          $this->t('Group manager: @name', ['@name' => $group->getOwner()->label()]),
          $this->t('Total members: @total', ['@total' => $memberships]),
          $this->t('Total content numbers: @total', ['@total' => $content]),
        ],
        '#cache' => [
          'contexts' => Cache::mergeContexts($group->getCacheContexts(), ['url']),
          'tags' => Cache::mergeTags($group->getCacheTags(), [
            'group_relationship_list:group:' . $group->id(),
          ]),
        ],
      ];
    }

    return [];
  }

}
