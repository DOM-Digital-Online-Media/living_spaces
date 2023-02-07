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

  /**
   * {@inheritdoc}
   */
  public function getEntityTypesOfLivingSpaceGroupTypes() {
    $types = $this->getLivingSpaceGroupTypes();
    $entity_types = [];
    foreach ($types as $type) {
      /** @var \Drupal\group\Entity\GroupContentType[] $group_content_types */
      $group_content_types = $this->entityTypeManager
        ->getStorage('group_content_type')
        ->loadByProperties(['group_type' => $type]);
      foreach ($group_content_types as $group_content_type) {
        $plugin = $group_content_type->getContentPlugin();
        $entity_type = $plugin->getEntityTypeId();
        $entity_types[$entity_type] = $entity_type;
      }
    }
    return $entity_types;
  }

}
