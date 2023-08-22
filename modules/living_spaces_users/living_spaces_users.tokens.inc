<?php

/**
 * @file
 * Contains living_spaces_users.tokens.inc.
 */

/**
 * Implements hook_token_info_alter().
 */
function living_spaces_users_token_info_alter(&$info) {
  // Remove useless token type.
  if (isset($info['types']['user-field_last_ip_address'])) {
    unset($info['types']['user-field_last_ip_address']);
  }
}
