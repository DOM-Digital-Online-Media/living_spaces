<?php

namespace Drupal\living_spaces_intranet;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Manager for ban related methods.
 */
class LivingSpacesBansManager implements LivingSpacesBansManagerInterface {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * LivingSpacesBansManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

}
