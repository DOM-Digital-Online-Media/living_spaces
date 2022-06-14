<?php

namespace Drupal\living_spaces_event\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for space event invite entities.
 */
interface LivingSpaceEventInviteInterface extends ContentEntityInterface, EntityOwnerInterface {}
