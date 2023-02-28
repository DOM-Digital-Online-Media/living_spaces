<?php

/**
 * @file
 * Contains living_spaces_event.theme.inc.
 */

use Drupal\Component\Utility\Html;

/**
 * Prepares variables for 'Fullcalendar legend' templates.
 */
function template_preprocess_living_spaces_event_fullcalendar_legend(&$variables) {
  $element = [];
  foreach ($variables['types'] as $type_name => $type) {
    $element[$type_name] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => $type['bundle'],
        'entity_type' => $type['entity_type'],
        'bundle' => $type['bundle'],
        'field' => $type['field_name'],
      ],
    ];

    $type['label'] = Html::escape($type['label']);
    $element[$type_name]['type'] = [
      '#markup' => $type['label'],
    ];
  }

  $variables['element'] = $element;
}
