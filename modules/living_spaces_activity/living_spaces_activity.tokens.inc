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
  $token_name = t('Rendered user image with image style');
  $tokens['message']['user_picture'] = [
    'name' => $token_name,
    'description' => t('Thumbnail'),
  ];
  $image_styles = image_style_options(FALSE);
  foreach ($image_styles as $style => $description) {
    $tokens['message']["user_picture:$style"] = [
      'name' => $token_name . ' ' . $description,
      'description' => t('Represents the image in the given image style.'),
    ];
  }

  return ['tokens' => $tokens];
}

/**
 * Implements hook_tokens().
 */
function living_spaces_activity_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'message' && !empty($data[$type])) {
    /** @var \Drupal\message\MessageInterface $message */
    $message = $data[$type];

    foreach ($tokens as $name => $original) {
      if ('greeting' == $name) {
        if ($message->getOwnerId() !== \Drupal::currentUser()->id()) {
          /** @var \Drupal\user\UserInterface $user */
          $replacements[$original] = $message->getOwner()->getAccountName() . ' ' . t('was');
        }
        else {
          $replacements[$original] = t('You were');
        }
      }
      $parts = explode(':', $name);
      if ('user_picture' == $parts[0]) {
        $user = $message->getOwner();
        if ($user->hasField('user_picture') && !$user->get('user_picture')->isEmpty()) {
          $style_name = $parts[1] ? $parts[1] : 'thumbnail';
          $image_uri = $user->user_picture->entity->getFileUri();

          $output = [
            '#theme' => 'image',
            '#style_name' => $style_name,
            '#uri' => $image_uri,
          ];

          /** @var \Drupal\Core\Render\RendererInterface $renderer */
          $renderer = \Drupal::service('renderer');
          $replacements[$original] = $renderer->renderPlain($output);
        }
      }
    }

  }

  return $replacements;
}
