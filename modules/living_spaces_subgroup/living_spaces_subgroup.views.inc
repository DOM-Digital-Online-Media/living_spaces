<?php

/**
 * @file
 * Contains living_spaces_subgroup.views.inc.
 */

/**
 * Implements hook_views_data_alter().
 */
function living_spaces_subgroup_views_data_alter(array &$data) {
  $data['groups_field_data']['living_spaces_subgroup_parent_field'] = [
    'title' => t('Group parent'),
    'field' => [
      'title' => t('Group parent'),
      'id' => 'living_spaces_subgroup_parent_field',
    ],
  ];
}
