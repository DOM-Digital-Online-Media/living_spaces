<?php

/**
 * @file
 * Contains living_spaces_activity.theme.inc.
 */

use Drupal\Core\Link;

/**
 * Prepares variables for views 'Notifications' templates.
 */
function template_preprocess_views_view_living_spaces_activity_notification(&$variables) {
  /** @var \Drupal\views\ViewExecutable $view */
  $view = $variables['view'];

  $options = $view->style_plugin->options;
  $variables['options'] = $options;
  $variables['options']['class'] .= " js-view-dom-id-{$view->id()}-{$view->current_display}";
  $variables['id'] = "js-view-dom-id-{$view->id()}-{$view->current_display}";

  $renderer = \Drupal::service('renderer');
  $variables['links'] = [];

  if ($variables['rows']) {
    foreach ($variables['rows'] as $row) {
      $markup = $renderer->render($row)->__toString();
      $variables['links'][] = ['#markup' => '<span class="dropdown-item-text">' . $markup . '</span>'];
    }

    $options = [
      'attributes' => [
        'id' => 'mark-all-messages',
        'class' => ['use-ajax', 'btn', 'btn-default', 'btn-sm'],
      ],
    ];

    $route = 'living_spaces_activity.mark_messages';
    $parameters = ['user' => \Drupal::currentUser()->id()];

    $footer = Link::createFromRoute(t('Mark all as read'), $route, $parameters, $options)->toString();
    $variables['links'][] = ['#markup' => '<span class="dropdown-item-text">' . $footer . '</span>'];
  }
  else {
    $variables['links'][] = ['#markup' => '<span class="dropdown-item-text">' . t('There are no new notifications for you.') . '</span>'];
  }
}
