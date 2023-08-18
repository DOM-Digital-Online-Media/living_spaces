<?php

namespace Drupal\living_spaces_subgroup;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Entity\GroupInterface;
use Psr\Log\LoggerInterface;

/**
 * Manager service to operate and get info on sub-group relations.
 */
class LivingSpacesSubgroupManager implements LivingSpacesSubgroupManagerInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Module logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new service object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupsParent(GroupInterface $group) {
    $parent = $group->get('parent')->referencedEntities();
    return $parent[0] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupsParents(GroupInterface $group, $include = TRUE) {
    $result = [];
    if ($include) {
      $result[$group->id()] = $group;
    }

    $parent = $group;
    while ($parent = $this->getGroupsParent($parent)) {
      $result = [$parent->id() => $parent] + $result;
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupsChildren(GroupInterface $group, $level = 1, $load = FALSE) {
    $results = [];
    $unlimited = $level === 0;
    $group_storage = $this->entityTypeManager->getStorage('group');

    $parents = [$group->id()];
    while (!empty($parents) && ($unlimited || $level-- > 0)) {
      $children = $group_storage
        ->getQuery()
        ->condition('parent', $parents, 'IN')
        ->accessCheck()
        ->execute();

      $parents = $children;
      foreach ($children as $child_id) {
        $results[] = $load ? $group_storage->load($child_id) : $child_id;
      }
    }

    return $results;
  }

}
