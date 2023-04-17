<?php

/**
 * @file
 * Contains living_spaces_group.views.inc.
 */

/**
 * Implements hook_views_data_alter().
 */
function living_spaces_group_views_data_alter(array &$data) {
  $data['groups_field_data']['living_spaces_group_is_living'] = [
    'title' => t('Living space'),
    'help' => t('Filter by group types that are marked as living spaces.'),
    'filter' => [
      'title' => t('Living space'),
      'field' => 'type',
      'id' => 'living_spaces_group_is_living',
    ],
  ];

  // Adds actions dropdown for space members.
  $data['users_field_data']['living_spaces_group_member_actions'] = [
    'title' => t('Space member actions'),
    'help' => t('Shows space member with dropdown for actions.'),
    'real field' => 'uid',
    'field' => [
      'id' => 'living_spaces_group_space_member_actions',
    ],
  ];

  // Add joined spaces titles from computed field.
  $data['user__joined_spaces']['living_spaces_group_joined_spaces'] = [
    'title' => t('Joined spaces titles'),
    'filter' => [
      'title' => t('Joined spaces titles'),
      'field' => 'joined_spaces_target_id',
      'id' => 'living_spaces_group_joined_spaces',
    ],
  ];

  $data['user__joined_spaces']['living_spaces_group_current_user_member'] = [
    'title' => t('Membership status'),
    'help' => t('I am a member of the space.'),
    'real field' => 'entity_id',
    'field' => [
      'id' => 'living_spaces_group_current_user_member',
    ],
  ];

  $data['groups_field_data']['living_spaces_group_user_joined_spaces'] = [
    'title' => t('Membership status'),
    'filter' => [
      'title' => t('I am a member of the space'),
      'field' => 'id',
      'id' => 'living_spaces_group_user_joined_spaces',
    ],
  ];

}
