<?php

namespace Drupal\living_spaces_group;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Manager for group related methods.
 */
class LivingSpacesGroupManager implements LivingSpacesGroupManagerInterface {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a LivingSpacesGroupManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function isLivingSpace(string $group_type) {
    $types = $this->getLivingSpaceGroupTypes();
    return is_array($types) && in_array($group_type, $types);
  }

  /**
   * {@inheritdoc}
   */
  public function getLivingSpaceGroupTypes() {
    $query = $this->entityTypeManager->getStorage('group_type')->getQuery();
    $query->condition('is_living_space', TRUE);
    $types = $query->execute();
    return is_array($types) ? $types : [];
  }

}
