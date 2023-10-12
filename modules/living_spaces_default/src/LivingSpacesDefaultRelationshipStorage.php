<?php

namespace Drupal\living_spaces_default;

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
    if ($entity->getGroup()->get('is_default')->getString() && 'group_membership' == $entity->getPluginId()) {
      // We cannot add an entity to a default group.
      return FALSE;
    }

    return parent::save($entity);
  }

}
