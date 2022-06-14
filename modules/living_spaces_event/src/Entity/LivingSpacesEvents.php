<?php

namespace Drupal\living_spaces_event\Entity;

use Drupal\living_spaces_sections\Entity\LivingSpacesSection;
use Drupal\Core\Link;

/**
 * Defines the living space events section entity.
 */
class LivingSpacesEvents extends LivingSpacesSection {

  /**
   * {@inheritdoc}
   */
  public function getSectionActions() {
    $actions = [];

    if ($group = $this->getGroup()) {
      $actions['create_event'] = Link::createFromRoute($this->t('Create Event'), 'living_spaces_group.create_content', [
        'group' => $group->id(),
        'plugin' => 'living_spaces_event',
      ])->toRenderable();

      if ($group->getGroupType()->hasContentPlugin('group_node:agenda')) {
        $actions['create_agenda'] = Link::createFromRoute($this->t('Create Agenda'), 'entity.group_content.create_form', [
          'group' => $group->id(),
          'plugin_id' => 'group_node:agenda',
        ])->toRenderable();
      }

      if ($group->getGroupType()->hasContentPlugin('group_node:protocol')) {
        $actions['create_protocol'] = Link::createFromRoute($this->t('Create Protocol'), 'entity.group_content.create_form', [
          'group' => $group->id(),
          'plugin_id' => 'group_node:protocol',
        ])->toRenderable();
      }
    }

    return array_merge($actions, parent::getSectionActions());
  }

}
