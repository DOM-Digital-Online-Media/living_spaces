<?php

namespace Drupal\living_spaces_group_privacy\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\node\NodeInterface;

/**
 * Describes basic method for living_spaces_group_privacy manager.
 */
interface LivingSpacesGroupPrivacyManagerInterface extends PluginManagerInterface {

  /**
   * Returns the list of all available plugin instances.
   *
   * @param \Drupal\group\Entity\GroupInterface|null $group
   *   Group object.
   *
   * @return \Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyInterface[]
   *   An array of plugin instances or a plugin instance for provided group or
   *   single item if group is provided.
   */
  public function getPrivacyPlugins(GroupInterface $group = NULL);

  /**
   * Returns the list of all related groups.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node object.
   *
   * @return \Drupal\group\Entity\GroupInterface[]
   *   An array of related groups.
   */
  public function getRelatedGroups(NodeInterface $node);

}
