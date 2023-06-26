<?php

/**
 * @file
 * Contains living_spaces_event.theme.inc.
 */

use Drupal\Component\Utility\Html;
use Drupal\living_spaces_event\Entity\LivingSpaceEventInviteInterface;

/**
 * Prepares variables for views 'Week style' templates.
 */
function template_preprocess_views_view_living_spaces_event_week(&$variables) {
  $options = $variables['view']->style_plugin->options;
  $variables['options'] = $options;

  /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
  $date_formatter = \Drupal::service('date.formatter');
  $monday = strtotime('monday this week 00:00:00');

  $weekdays = [
    t('Mon', [], ['context' => 'Abbreviated weekday']),
    t('Tue', [], ['context' => 'Abbreviated weekday']),
    t('Wed', [], ['context' => 'Abbreviated weekday']),
    t('Thu', [], ['context' => 'Abbreviated weekday']),
    t('Fri', [], ['context' => 'Abbreviated weekday']),
    t('Sat', [], ['context' => 'Abbreviated weekday']),
    t('Sun', [], ['context' => 'Abbreviated weekday']),
  ];

  $items = [];
  for ($i = 0; $i < 7; $i++) {
    $time = $i != 0 ? strtotime('+1 day', $time) : $monday;
    $today = $date_formatter->format($time, 'custom', 'd.m') == $date_formatter->format(\Drupal::time()->getRequestTime(), 'custom', 'd.m');
    $active = $today ? 'active' : '';

    $items[$i] = [
      'day' => $weekdays[$i],
      'date' => $date_formatter->format($time, 'custom', 'd.m'),
      'class' => $active,
      'items' => [],
    ];
  }

  $variables['#unprocessed_rows'] = [];
  foreach ($variables['rows'] as $row) {
    /** @var \Drupal\group\Entity\GroupRelationshipInterface $content */
    $content = $row['#row']->_entity;
    $event = NULL;

    if ($content instanceof LivingSpaceEventInviteInterface) {
      /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInterface $event */
      $event = $content->get('event')->entity;
    }
    else {
      /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInterface $event */
      if (method_exists($content, 'getEntity')) {
        $event = $content->getEntity();
      }
    }

    if ($event && $event->hasField('field_start_date')) {
      $start = strtotime($event->get('field_start_date')->getValue()[0]['value']);
      $items[$date_formatter->format($start, 'custom', 'N') - 1]['items'][$event->id()] = [
        'content' => $row,
      ];
    }
    else {
      // Save unprocessed rows for next hook_preprocess_HOOK()
      $variables['#unprocessed_rows'][] = $row;
    }
  }

  $variables['rows'] = $items;
}

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
