<?php

namespace Drupal\living_spaces_subgroup;

use Drupal\group\Entity\GroupInterface;

/**
 * Interface for living spaces sub-groups manager service.
 */
interface LivingSpacesSubgroupManagerInterface {

  /**
   * Returns parent group to given group entity.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group entity.
   *
   * @return \Drupal\group\Entity\GroupInterface|null
   *   Parent group entity if exists.
   */
  public function getGroupsParent(GroupInterface $group);

  /**
   * Returns all parents of group going from root.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group entity.
   * @param bool $include
   *   Whether to include given group in result chain.
   *
   * @return \Drupal\group\Entity\GroupInterface[]
   *   Chain of parents, from root to the given group.
   */
  public function getGroupsParents(GroupInterface $group, $include = TRUE);

  /**
   * Returns children groups to given group entity up to certain level.
   *
   * Note that this method may hit performance, so use it with caution making
   * sure arguments fit what is expected to be returned.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group entity.
   * @param int $level
   *   Maximum level of children to return. 0 to return all.
   * @param bool $load
   *   Whether to load entity and return entities or return only group ids.
   *
   * @return \Drupal\group\Entity\GroupInterface[]|int[]
   *   Children group entities or entity ids array.
   */
  public function getGroupsChildren(GroupInterface $group, $level = 1, $load = FALSE);

}
