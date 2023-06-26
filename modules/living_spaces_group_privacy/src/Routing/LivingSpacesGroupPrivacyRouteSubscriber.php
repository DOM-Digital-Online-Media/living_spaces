<?php

namespace Drupal\living_spaces_group_privacy\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * LivingSpacesGroupPrivacyRouteSubscriber class.
 */
class LivingSpacesGroupPrivacyRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.group_relationship.add_form')) {
      $route->addRequirements(['_living_spaces_group_privacy_access_check' => '1']);
    }
    if ($route = $collection->get('entity.group.join')) {
      $route->addRequirements(['_living_spaces_group_privacy_access_check' => '1']);
    }
  }

}
