<?php

/**
 * @file
 * Contains living_spaces_sections.views.inc.
 */

/**
 * Implements hook_views_data_alter().
 */
function living_spaces_sections_views_data_alter(array &$data) {
  $data['views']['section_path'] = [
    'title' => t('Section path'),
    'area' => [
      'id' => 'section_path',
    ],
  ];
}
