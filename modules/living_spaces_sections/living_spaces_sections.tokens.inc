<?php

/**
 * @file
 * Builds placeholder replacement tokens for space section data.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function living_spaces_sections_token_info() {
  $tokens['group_content']['living_spaces_section'] = [
    'name' => t('Section'),
    'type' => 'living_spaces_section',
  ];
  $tokens['group_content']['entity_id_url'] = [
    'name' => t('Entity URL'),
  ];

  return ['tokens' => $tokens];
}

/**
 * Implements hook_tokens().
 */
function living_spaces_sections_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'group_content' && !empty($data[$type])) {
    /** @var \Drupal\group\Entity\GroupContentInterface $group_content */
    $group_content = $data['group_content'];

    foreach ($tokens as $name => $original) {
      if ('living_spaces_section' == $name) {
        if (!empty($group_content->section->getValue())) {
          $replacements[$original] = $group_content->section->entity->label();
        }
      }
      if ('entity_id_url' == $name) {
        $replacements[$original] = $group_content->getEntity()->toUrl()->toString();
      }
    }

    if (!empty($group_content->section->getValue()) && $living_spaces_section_tokens = \Drupal::token()->findWithPrefix($tokens, 'living_spaces_section')) {
      $replacements += \Drupal::token()->generate('living_spaces_section', $living_spaces_section_tokens, ['living_spaces_section' => $group_content->section->entity], $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
