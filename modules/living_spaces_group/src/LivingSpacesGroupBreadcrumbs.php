<?php

namespace Drupal\living_spaces_group;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Primary implementation for the Breadcrumb builder.
 */
class LivingSpacesGroupBreadcrumbs implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * Returns the module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Returns the config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Constructs a LivingSpacesGroupBreadcrumbs object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Interface for classes that manage a set of enabled modules.
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Defines the configuration object factory.
   */
  public function __construct(ModuleHandlerInterface $module_handler, ConfigFactory $config) {
    $this->moduleHandler = $module_handler;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $breadcrumbs = $this->moduleHandler->invokeAll('living_spaces_breadcrumbs_info', [$route_match]);

    return in_array(TRUE, $breadcrumbs);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['route', 'url.path', 'languages']);
    $breadcrumb->addCacheableDependency($route_match);

    $home = $this->config->get('easy_breadcrumb.settings')->get('home_segment_title');
    $breadcrumb->addLink(Link::createFromRoute(!empty($home) ? $home : $this->t('Start'), '<front>'));
    $this->moduleHandler->invokeAll('living_spaces_breadcrumbs_info', [$route_match, $breadcrumb]);;

    return $breadcrumb;
  }

}
