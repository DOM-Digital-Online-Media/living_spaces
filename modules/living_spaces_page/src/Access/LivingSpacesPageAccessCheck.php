<?php

namespace Drupal\living_spaces_page\Access;

use Drupal\node\Access\NodeAddAccessCheck;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeTypeInterface;
use Drupal\Core\Access\AccessResult;

/**
 * LivingSpacesPageAccessCheck class.
 */
class LivingSpacesPageAccessCheck extends NodeAddAccessCheck {

  /**
   * Check access for node.add route.
   */
  public function access(AccountInterface $account, NodeTypeInterface $node_type = NULL) {
    if (isset($node_type) && 'page' == $node_type->id() && $space = \Drupal::request()->query->get('space')) {
      /** @var \Drupal\group\Entity\Group $group */
      if ($group = $this->entityTypeManager->getStorage('group')->load($space)) {
        if ($group->hasPermission('create page entities', $account)) {
          return AccessResult::allowed();
        }
      }
    }

    return parent::access($account, $node_type);
  }

}
