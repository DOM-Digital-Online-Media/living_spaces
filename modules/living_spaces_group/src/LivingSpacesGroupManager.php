<?php

namespace Drupal\living_spaces_group;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;

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
      /** @var \Drupal\group\Entity\GroupRelationshipTypeInterface[] $relationship_types */
      $relationship_types = $this->entityTypeManager
        ->getStorage('group_relationship_type')
        ->loadByProperties(['group_type' => $type]);
      foreach ($relationship_types as $relationship_type) {
        $type = $relationship_type->getPlugin()->getRelationType()->getEntityTypeId();
        $entity_types[$type] = $type;
      }
    }
    return $entity_types;
  }

  /**
   * {@inheritdoc}
   */
  public function isUserSpaceAdmin(AccountInterface $account, GroupInterface $group) {
    /** @var \Drupal\group\Entity\Storage\GroupRoleStorageInterface $role_storage */
    $role_storage = $this->entityTypeManager->getStorage('group_role');
    $roles = $role_storage->loadByUserAndGroup($account, $group);
    foreach ($roles as $role) {
      if ($role->get('is_space_admin')) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
