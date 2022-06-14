<?php

namespace Drupal\living_spaces_page\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * LivingSpacesPageRouteSubscriber class.
 */
class LivingSpacesPageRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('node.add')) {
      $route->setRequirements(['_living_spaces_page_access_check' => '1']);
    }
  }

}
