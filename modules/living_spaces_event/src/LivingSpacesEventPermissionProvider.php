<?php

namespace Drupal\living_spaces_event;

use Drupal\group\Plugin\Group\RelationHandler\PermissionProviderInterface;
use Drupal\group\Plugin\Group\RelationHandler\PermissionProviderTrait;

/**
 * Provides group permissions for living_spaces_event GroupContent entities.
 */
class LivingSpacesEventPermissionProvider implements PermissionProviderInterface {
  use PermissionProviderTrait;

  /**
   * Constructs a new LivingSpacesEventPermissionProvider.
   *
   * @param \Drupal\group\Plugin\Group\RelationHandler\PermissionProviderInterface $parent
   *   The parent permission provider.
   */
  public function __construct(PermissionProviderInterface $parent) {
    $this->parent = $parent;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityViewUnpublishedPermission($scope = 'any') {
    if ($this->definesEntityPermissions) {
      if ($this->implementsPublishedInterface) {
        if ($scope === 'any') {
          return "view unpublished $this->pluginId entity";
        }
      }
    }

    return $this->parent->getEntityViewUnpublishedPermission($scope);
  }

}
