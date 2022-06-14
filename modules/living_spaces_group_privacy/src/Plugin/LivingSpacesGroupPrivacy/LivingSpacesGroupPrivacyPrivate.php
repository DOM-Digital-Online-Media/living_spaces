<?php

namespace Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacy;

use Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyBase;
use Drupal\node\NodeInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides private privacy plugin.
 *
 * @LivingSpacesGroupPrivacy(
 *   id = "private",
 *   label = @Translation("Private"),
 *   default = TRUE,
 * )
 */
class LivingSpacesGroupPrivacyPrivate extends LivingSpacesGroupPrivacyBase {

  /**
   * {@inheritdoc}
   */
  public function getGroupNodeGrants(NodeInterface $node, GroupInterface $group) {
    $grants = [];

    $grants[] = [
      'realm' => 'living_node_group_member',
      'gid' => $group->id(),
      'grant_view' => 1,
      'grant_update' => 0,
      'grant_delete' => 0,
    ];

    return $grants;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupUserGrants(AccountInterface $account, $op) {
    $grants = [];

    foreach ($this->membershipLoader->loadByUser($account) as $membership) {
      /** @var \Drupal\group\Entity\Group $group */
      $group = $membership->getGroup();
      $grants['living_node_group_member'][] = $group->id();
    }

    return $grants;
  }

}
