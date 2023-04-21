<?php

namespace Drupal\living_spaces_group;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Primary implementation for the Easy Breadcrumb builder.
 */
class LivingSpacesGroupBreadcrumbs implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $parameters = $route_match->getParameters()->all();
    if (isset($parameters['node'])) {
      return $parameters['node']->getType() === 'article';
    }
    if (isset($parameters['term'])) {
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();

    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));

    return $breadcrumb;
  }

}
