<?php

/**
 * @file
 * Hooks specific to the Living spaces event module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Provide a list of event actions for provided space.
 *
 * @param \Drupal\group\Entity\GroupInterface $space
 *   Related space entity.
 *
 * @return array
 *   An array of event actions.
 */
function hook_living_spaces_event_action_info(\Drupal\group\Entity\GroupInterface $space) {
  $items = [];

  $items['create_event'] = [
    '#type' => 'link',
    '#title' => t('Create Event'),
    '#options' => [],
    '#url' => \Drupal\Core\Url::fromRoute('living_spaces_group.create_content', [
      'group' => $space->id(),
      'plugin' => 'living_spaces_event',
    ]),
  ];

  return $items;
}

/**
 * @} End of "addtogroup hooks".
 */
