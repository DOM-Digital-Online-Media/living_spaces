<?php

namespace Drupal\living_spaces_subgroup;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\living_spaces_subgroup\LivingSpacesSubgroupManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Primary implementation for the Breadcrumb builder.
 */
class LivingSpacesSubGroupBreadcrumbs implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * Returns the config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Returns the living_spaces_subgroup.manager service.
   *
   * @var \Drupal\living_spaces_subgroup\LivingSpacesSubgroupManagerInterface
   */
  protected $subGroupManager;

  /**
   * Constructs a LivingSpacesSubGroupBreadcrumbs object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Defines the configuration object factory.
   *  @param \Drupal\living_spaces_subgroup\LivingSpacesSubgroupManagerInterface $sub_group_manager
   *   Interface for living spaces sub-groups manager service.
   */
  public function __construct(ConfigFactory $config, LivingSpacesSubgroupManagerInterface $sub_group_manager) {
    $this->config = $config;
    $this->subGroupManager = $sub_group_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    if ($route_match->getRouteName() === 'entity.group.canonical') {
      $group = $route_match->getParameter('group');

      if ($parents = $this->subGroupManager->getGroupsParents($group, FALSE)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['route', 'url.path', 'languages']);

    $home = \Drupal::config('easy_breadcrumb.settings')->get('home_segment_title');
    $breadcrumb->addLink(Link::createFromRoute(!empty($home) ? $home : t('Start'), '<front>'));

    $group = $route_match->getParameter('group');
    $parents = $this->subGroupManager->getGroupsParents($group, FALSE);

    foreach ($parents as $parent) {
      $breadcrumb->addLink($parent->toLink());
      $breadcrumb->addCacheableDependency($parent);
    }

    $breadcrumb->addLink(Link::createFromRoute($group->label(), '<none>'));
    $breadcrumb->addCacheableDependency($group);

    return $breadcrumb;
  }

}
