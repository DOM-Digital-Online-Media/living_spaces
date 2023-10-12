<?php

namespace Drupal\living_spaces_default;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\group\Entity\Storage\GroupRelationshipStorage;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the storage handler class for relationship entities.
 */
class LivingSpacesDefaultRelationshipStorage extends GroupRelationshipStorage {

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $entity) {
    if ($entity->getGroup()->get('is_default')->getString()) {
      // We cannot add an entity to a default group.
      return;
    }

    return parent::save($entity);
  }

}
