<?php

namespace Drupal\living_spaces_event\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for space event entities.
 */
interface LivingSpaceEventInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Gets the space event type entity.
   *
   * @return \Drupal\living_spaces_event\Entity\LivingSpaceEventTypeInterface
   *   The space event type entity.
   */
  public function getEventType();

}
