<?php

namespace Drupal\living_spaces_intranet\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for ban entities.
 */
interface LivingSpacesBanInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {}
