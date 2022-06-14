<?php

namespace Drupal\living_spaces_discussions\Entity;

use Drupal\living_spaces_sections\Entity\LivingSpacesSection;
use Drupal\Core\Link;

/**
 * Defines the living space discussions section entity.
 */
class LivingSpacesDiscussions extends LivingSpacesSection {

  /**
   * {@inheritdoc}
   */
  public function getSectionActions() {
    $actions = [];

    if ($group = $this->getGroup()) {
      if ($group->getGroupType()->hasContentPlugin('group_node:discussion_post')) {
        $actions['create_discussion'] = Link::createFromRoute($this->t('Create discussion'), 'entity.group_content.create_form', [
          'group' => $group->id(),
          'plugin_id' => 'group_node:discussion_post',
        ])->toRenderable();
      }
    }

    return array_merge($actions, parent::getSectionActions());
  }

}
