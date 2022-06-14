<?php

namespace Drupal\living_spaces_subgroup;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Access\GroupAccessControlHandler;

/**
 * Group access control handler to resolve sub-groups access.
 */
class LivingSpacesSubgroupAccessControlHandler extends GroupAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\group\Entity\GroupInterface $entity */
    $result = parent::checkAccess($entity, $operation, $account);

    /** @var \Drupal\living_spaces_subgroup\LivingSpacesSubgroupManagerInterface $manager */
    $manager = \Drupal::service('living_spaces_subgroup.manager');

    // If user does not have access to the group, check parent instead.
    if ($result->isForbidden() && ($parent = $manager->getGroupsParent($entity))) {
      return $this->checkAccess($parent, $operation, $account);
    }

    return $result;
  }

}
