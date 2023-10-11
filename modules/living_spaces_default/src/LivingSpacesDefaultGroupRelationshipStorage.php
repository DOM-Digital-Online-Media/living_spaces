<?php

namespace Drupal\living_spaces_default;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\group\Entity\Storage\GroupRelationshipStorage;
use Drupal\Core\Entity\EntityInterface;
use Drupal\group\Entity\GroupInterface;;

/**
 * Defines the storage handler class for relationship entities.
 */
class LivingSpacesDefaultGroupRelationshipStorage extends GroupRelationshipStorage {

  /**
   * {@inheritdoc}
   */
  public function createForEntityInGroup(EntityInterface $entity, GroupInterface $group, $plugin_id, $values = []) {
    if ($group->get('is_default')->getString()) {
      throw new EntityStorageException('Cannot add an entity to a default group.');
    }

    return parent::createForEntityInGroup($entity, $group, $plugin_id, $values);
  }

}
