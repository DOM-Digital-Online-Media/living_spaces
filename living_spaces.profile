<?php

/**
 * @file
 * Enables modules and site configuration for a living_spaces site installation.
 */

/**
 * Implements hook_theme_registry_alter().
 */
function living_spaces_theme_registry_alter(&$theme_registry) {
  if (!empty($theme_registry['page_title'])) {
    $theme_registry['page_title']['variables']['lead'] = '';
    $theme_registry['page_title']['variables']['include_hr'] = '';
  }
}
