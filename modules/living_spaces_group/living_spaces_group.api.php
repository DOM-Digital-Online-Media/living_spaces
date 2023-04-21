<?php

/**
 * @file
 * Hooks specific to the Living spaces group module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Provide information about specific group type.
 *
 * @return array
 *   An array of group type information, containing:
 *    - parent_type: (optional) allowed parent space types;
 *    - title_callback: (optional) callback function for default title.
 *   Other modules may provide other info as well.
 */
function hook_living_spaces_group_type_info() {
  $info = [];

  $info['standard'] = [
    'parent_types' => ['my_group_type'],
    'title_callback' => 'my_group_type_title_callback',
  ];

  return $info;
}

/**
 * Provide action links for a space and particular member.
 *
 * @param bool $names_only
 *   Whether to return action labels or actions with links for group and member.
 *   In both variants arrays should be keyed with same keys.
 * @param \Drupal\group\Entity\GroupInterface|null $space
 *   Living space group entity, required if $names_only is FALSE.
 * @param \Drupal\user\UserInterface|null $user
 *   User for which actions are shown, required if $names_only is FALSE.
 *
 * @return array
 *   If $names_only param is TRUE then label of actions will be returned,
 *   if it is FALSE then render arrays for action links returned.
 */
function hook_living_spaces_group_actions_info($names_only = TRUE, \Drupal\group\Entity\GroupInterface $space = NULL, \Drupal\user\UserInterface $user = NULL) {
  return [
    'remove_from_space' => $names_only ? t('Remove from space') : [
      '#type' => 'link',
      '#title' => t('Remove from space'),
      '#options' => [],
      '#url' => \Drupal\Core\Url::fromRoute('remove_from_space_route', [
        'group' => $space->id(),
        'user' => $user->id(),
      ]),
    ],
  ];
}

/**
 * Provide contact links for a space and particular member.
 *
 * @param bool $names_only
 *   Whether to return contact action label or links for group and member.
 *   In both variants arrays should be keyed with same keys.
 * @param \Drupal\group\Entity\GroupInterface|null $space
 *   Living space group entity, required if $names_only is FALSE.
 *
 * @return array
 *   If $names_only param is TRUE then label of contact will be returned,
 *   if it is FALSE then render arrays for contact links returned.
 */
function hook_living_spaces_group_contact_info($names_only = TRUE, \Drupal\group\Entity\GroupInterface $space = NULL) {
  return [
    'remove_from_space' => $names_only ? t('Message all members') : [
      '#type' => 'link',
      '#title' => t('Message all members'),
      '#options' => [],
      '#url' => \Drupal\Core\Url::fromRoute('message_module.message_members_route', [
        'group' => $space->id(),
      ]),
    ],
  ];
}

/**
 * Provide export links for a space and particular member.
 *
 * @param bool $names_only
 *   Whether to return export names or export links for group and member.
 *   In both variants arrays should be keyed with same keys.
 * @param \Drupal\group\Entity\GroupInterface|null $space
 *   Living space group entity, required if $names_only is FALSE.
 *
 * @return array
 *   If $names_only param is TRUE then label of exports will be returned,
 *   if it is FALSE then render arrays for export links returned.
 */
function hook_living_spaces_group_exports_info($names_only = TRUE, \Drupal\group\Entity\GroupInterface $space = NULL) {
  return [
    'export_all' => $names_only ? t('Export all members data') : [
      '#type' => 'link',
      '#title' => t('Export all members data'),
      '#options' => [],
      '#url' => \Drupal\Core\Url::fromRoute('export_route', [
        'group' => $space->id(),
      ]),
    ],
  ];
}

/**
 * Provide a list of entities to remove with their entity types.
 *
 * @param \Drupal\group\Entity\GroupInterface $space
 *   Related space group entity.
 * @param array $items
 *   An array of entities to remove.
 */
function hook_living_spaces_group_remove_group_content_alter(\Drupal\group\Entity\GroupInterface $space, array &$items) {
  $items['group'][] = 1;
}

/**
 * Provide a list of actions for provided space.
 *
 * @param \Drupal\group\Entity\GroupInterface $space
 *   Related space entity.
 *
 * @return array
 *   An array of space actions.
 */
function hook_living_spaces_group_action_info(\Drupal\group\Entity\GroupInterface $space) {
  $items = [];

  $items['view'] = [
    '#type' => 'link',
    '#title' => t('View'),
    '#options' => [],
    '#url' => \Drupal\Core\Url::fromRoute('entity.group.canonical', [
      'group' => $space->id(),
    ]),
  ];

  return $items;
}

/**
 * Provide a list of breadcrumbs for provided route.
 *
 * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
 *   Current route.
 *
 * @return array
 *   An array of breadcrumbs configs.
 */
function hook_living_spaces_breadcrumbs_info(\Drupal\Core\Routing\RouteMatchInterface $route_match) {
  if ('entity.group.canonical' == $route_match->getRouteName()) {
    $parameters = $route_match->getParameters()->all();

    return [
      'applies' => TRUE,
      'breadcrumbs' => [
        \Drupal\Core\Link::createFromRoute($parameters['group']->label(), '<none>')
      ],
    ];
  }

  return [];
}

/**
 * Provide a list of permissions to exclude for office managers.
 *
 * @return array
 *   An array of permissions.
 */
function hook_living_spaces_group_exclude_permissions() {
  $permissions = [];

  $permissions[] = 'manage space members';

  return $permissions;
}

/**
 * @} End of "addtogroup hooks".
 */
