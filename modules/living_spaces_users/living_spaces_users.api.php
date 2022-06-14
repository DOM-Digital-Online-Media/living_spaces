<?php

/**
 * @file
 * Hooks specific to the Living spaces users module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Provides notification links.
 *
 * @return array
 *   An array of notification links.
 */
function hook_living_spaces_users_notification_menu_item_info() {
  return [
    'logout' => [
      'content' => \Drupal\Core\Link::createFromRoute(t('Logout'), 'user.logout'),
      '#attributes' => ['class' => ['nav-item']],
      'weight' => 0,
    ],
  ];
}

/**
 * Alter notification links.
 *
 * @param array $links
 *   An array of notification menu links.
 */
function hook_living_spaces_users_notification_menu_item_info_alter(array $links) {
  if (isset($links['logout'])) {
    unset($links['logout']);
  }
}

/**
 * @} End of "addtogroup hooks".
 */
