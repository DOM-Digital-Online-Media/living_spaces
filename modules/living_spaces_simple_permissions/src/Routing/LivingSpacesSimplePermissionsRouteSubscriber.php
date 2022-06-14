<?php

namespace Drupal\living_spaces_simple_permissions\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * LivingSpacesSimplePermissionsRouteSubscriber class.
 */
class LivingSpacesSimplePermissionsRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $routes = [
      'user.admin_permissions',
      'entity.user_role.edit_permissions_form',
      'entity.group_type.permissions_form',
      'entity.group_type.outsider_permissions_form',
    ];
    foreach ($routes as $item) {
      if ($route = $collection->get($item)) {
        $route->setRequirements(['_permission' => 'access simple permissions']);
      }
    }
  }

}
