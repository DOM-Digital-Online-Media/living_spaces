<?php

/**
 * @file
 * Hooks specific to the living spaces subgroup module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Provide action links for a child group.
 *
 * @param \Drupal\group\Entity\GroupInterface $child
 *   Child group for which actions are shown.
 *
 * @return array
 *   An array of child group actions.
 */
function hook_living_spaces_subgroup_child_actions_info(\Drupal\group\Entity\GroupInterface $child) {
  $links = [];

  $links['remove_child'] = [
    '#type' => 'link',
    '#title' => t('Remove child'),
    '#options' => [],
    '#url' => \Drupal\Core\Url::fromRoute('living_spaces_subgroup.remove_child', [
      'child' => $child->id(),
    ]),
  ];

  return $links;
}

/**
 * @} End of "addtogroup hooks".
 */
