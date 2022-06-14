<?php

namespace Drupal\living_spaces_group_privacy\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\node\NodeInterface;

/**
 * Defines an interface for plugins.
 */
interface LivingSpacesGroupPrivacyInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  /**
   * Returns node grants for the node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node object.
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group object.
   *
   * @return array
   *   An array of related grants.
   */
  public function getGroupNodeGrants(NodeInterface $node, GroupInterface $group);

  /**
   * Returns grants for the user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Account object.
   * @param string $op
   *   Operation name.
   *
   * @return array
   *   An array of related grants.
   */
  public function getGroupUserGrants(AccountInterface $account, $op);

  /**
   * Returns join group access check.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group object.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Account object.
   *
   * @return bool
   *   Access check result.
   */
  public function joinAccess(GroupInterface $group, AccountInterface $account);

  /**
   * Returns join group access check.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group object.
   * @param string $operation
   *   The operation that is to be performed on $group.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Account object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Access check result.
   */
  public function groupAccess(GroupInterface $group, $operation, AccountInterface $account);

}
