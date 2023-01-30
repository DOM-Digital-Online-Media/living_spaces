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
    $theme_registry['page_title']['variables']['lead'] = [];
    $theme_registry['page_title']['variables']['include_hr'] = 0;
  }
}

/**
 * Implements hook_dip_translations_default_options_alter().
 */
function living_spaces_dip_translations_default_options_alter(&$options, $path) {
  if (strpos($path, 'living_spaces_') !== FALSE || strpos(strrev($path), strrev('living_spaces')) === 0) {
    $options[] = $path;
  }
}
