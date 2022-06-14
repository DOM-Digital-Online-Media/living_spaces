<?php

/**
 * @file
 * Contains living_spaces_group_privacy.views.inc.
 */

/**
 * Implements hook_views_data_alter().
 */
function living_spaces_group_privacy_views_data_alter(array &$data) {
  $data['groups_field_data']['living_spaces_group_privacy_field'] = [
    'title' => t('Group privacy'),
    'field' => [
      'title' => t('Group privacy'),
      'id' => 'living_spaces_group_privacy_field',
    ],
    'filter' => [
      'title' => t('Group privacy'),
      'field' => 'living_spaces_group_privacy_field',
      'id' => 'living_spaces_group_privacy_filter',
    ],
  ];
}
