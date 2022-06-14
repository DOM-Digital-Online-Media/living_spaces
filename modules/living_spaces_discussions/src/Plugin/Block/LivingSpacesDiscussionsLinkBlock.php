<?php

namespace Drupal\living_spaces_discussions\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Block with 'LivingSpacesDiscussionsLinkBlock' block.
 *
 * @Block(
 *   id = "living_spaces_discussions_link_block",
 *   admin_label = @Translation("Discussion link"),
 *   category = @Translation("Living Spaces"),
 * )
 */
class LivingSpacesDiscussionsLinkBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the current_route_match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $route;

  /**
   * Returns the living_spaces_sections.manager service.
   *
   * @var \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface
   */
  protected $sectionManager;

  /**
   * Constructs a LivingSpacesDiscussionsLinkBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   Provides an interface for classes representing the result of routing.
   * @param \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface $section_manager
   *   Interface for section manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route, LivingSpacesSectionsManagerInterface $section_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->route = $route;
    $this->sectionManager = $section_manager;
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
      $container->get('living_spaces_sections.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    if ($group = $this->route->getParameter('group')) {
      if ($section = $this->sectionManager->getSectionFromGroupByType($group, 'discussions')) {
        return [
          '#markup' => $section->toLink($this->t('All discussions'))->toString(),
        ];
      }
    }

    return [];
  }

}
