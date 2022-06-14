<?php

namespace Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacy;

use Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyBase;
use Drupal\node\NodeInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides public privacy plugin.
 *
 * @LivingSpacesGroupPrivacy(
 *   id = "public",
 *   label = @Translation("Public"),
 *   default = FALSE,
 * )
 */
class LivingSpacesGroupPrivacyPublic extends LivingSpacesGroupPrivacyBase {

  /**
   * {@inheritdoc}
   */
  public function getGroupNodeGrants(NodeInterface $node, GroupInterface $group) {
    $grants = [];

    $grants[] = [
      'realm' => 'living_node_public_group',
      'gid' => LIVING_SPACES_PRIVACY_PUBLIC_GROUP,
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

    if ($account->isAuthenticated()) {
      $grants['living_node_public_group'][] = LIVING_SPACES_PRIVACY_PUBLIC_GROUP;
    }

    return $grants;
  }

}
