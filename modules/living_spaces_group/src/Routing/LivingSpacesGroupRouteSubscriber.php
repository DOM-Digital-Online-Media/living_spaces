<?php

namespace Drupal\living_spaces_group\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * LivingSpacesGroupRouteSubscriber class.
 */
class LivingSpacesGroupRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.group_relationship.delete_form')) {
      $route->addDefaults(['_title_callback' => '\Drupal\living_spaces_group\Controller\LivingSpacesGroupEntityController::deleteTitle']);
    }
    if ($route = $collection->get('filter.tips')) {
      $route->setRequirement('_access', 'FALSE');
    }
    if ($route = $collection->get('filter.tips_all')) {
      $route->setRequirement('_access', 'FALSE');
    }
    if ($route = $collection->get('dblog.overview')) {
      $route->setDefault('_controller', '\Drupal\living_spaces_group\Controller\LivingSpacesGroupLogController::overview');
    }
  }

}
