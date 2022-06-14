<?php

namespace Drupal\living_spaces_event\Plugin;

use Drupal\group\Plugin\GroupContentPermissionProvider;

/**
 * Provides group permissions for living_spaces_event GroupContent entities.
 */
class LivingSpacesEventPermissionProvider extends GroupContentPermissionProvider {

  /**
   * {@inheritdoc}
   */
  public function getEntityViewUnpublishedPermission($scope = 'any') {
    if ($scope === 'any') {
      return "view unpublished $this->pluginId entity";
    }

    return parent::getEntityViewUnpublishedPermission($scope);
  }

}
