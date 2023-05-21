<?php

/**
 * @file
 * Builds placeholder replacement tokens for living_spaces_activity.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function living_spaces_activity_token_info() {
  $tokens['message']['greeting'] = [
    'name' => t('Greeting in personal or Username'),
  ];

  return ['tokens' => $tokens];
}

/**
 * Implements hook_tokens().
 */
function living_spaces_activity_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  // $type = "entity"
  // $data['entity_type'] = "message"
  // $data['entity']

  if ($type == 'message' && !empty($data[$type])) {
    /** @var \Drupal\message\MessageInterface $message */
    $message = $data[$type];

    foreach ($tokens as $name => $original) {
      if ('greeting' == $name) {
        if ($message->hasField('uid') && $message->uid->target_id !== \Drupal::currentUser()->id()) {
          /** @var \Drupal\user\UserInterface $user */
          $user = $message->uid->entity;
          $replacements[$original] = $user->getAccountName() . ' ' . t('was');
        }
        else {
          $replacements[$original] = t('You were');
        }
      }
    }

  }

  return $replacements;
}
