<?php

namespace Drupal\living_spaces_default\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * LivingSpacesDefaultRouteSubscriber class.
 */
class LivingSpacesDefaultRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.group_relationship.add_form')) {
      $route->addRequirements(['_living_spaces_default_access_check' => '1']);
    }
    if ($route = $collection->get('entity.group.join')) {
      $route->addRequirements(['_living_spaces_default_access_check' => '1']);
    }
    if ($route = $collection->get('entity.group_relationship.create_form')) {
      $route->addRequirements(['_living_spaces_default_create_form_access' => '1']);
    }
    if ($route = $collection->get('entity.group_relationship.create_page')) {
      $route->addRequirements(['_living_spaces_default_create_page_access' => '1']);
    }
  }

}
