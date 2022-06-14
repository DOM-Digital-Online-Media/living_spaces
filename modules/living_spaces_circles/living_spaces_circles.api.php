<?php

/**
 * @file
 * Hooks specific to the living spaces circles module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Provide action links for a circle group.
 *
 * @param \Drupal\group\Entity\GroupInterface $group
 *   Group that has the circle groups.
 * @param \Drupal\group\Entity\GroupInterface $circle
 *   Circle group for which actions are shown.
 *
 * @return array
 *   An array of circle group actions.
 */
function hook_living_spaces_circles_actions_info(\Drupal\group\Entity\GroupInterface $group, \Drupal\group\Entity\GroupInterface $circle) {
  $links = [];

  $links['remove_circle'] = [
    '#type' => 'link',
    '#title' => t('Remove circle'),
    '#options' => [],
    '#url' => \Drupal\Core\Url::fromRoute('living_spaces_circles.remove_circle', [
      'group' => $group->id(),
      'circle' => $circle->id(),
    ]),
  ];

  return $links;
}

/**
 * @} End of "addtogroup hooks".
 */
