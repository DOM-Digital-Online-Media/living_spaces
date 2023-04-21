<?php

namespace Drupal\living_spaces_group;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
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
   * Constructs a LivingSpacesGroupBreadcrumbs object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Interface for classes that manage a set of enabled modules.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $breadcrumbs = $this->moduleHandler->invokeAll('living_spaces_breadcrumbs_info', [$route_match]);

    return isset($breadcrumbs['applies']) && $breadcrumbs['applies'];
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));

    $items = $this->moduleHandler->invokeAll('living_spaces_breadcrumbs_info', [$route_match]);
    foreach ($items['breadcrumbs'] as $item) {
      $breadcrumb->addLink($item);
    }

    return $breadcrumb;
  }

}
