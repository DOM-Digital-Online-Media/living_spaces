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
    if ($route = $collection->get('entity.group_content.delete_form')) {
      $route->addDefaults(['_title_callback' => '\Drupal\living_spaces_group\LivingSpacesGroupEntityController::deleteTitle']);
    }
  }

}
