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
  $tokens['group_relationship']['living_spaces_section'] = [
    'name' => t('Section'),
    'type' => 'living_spaces_section',
  ];
  $tokens['group_relationship']['entity_id_url'] = [
    'name' => t('Entity URL'),
  ];

  return ['tokens' => $tokens];
}

/**
 * Implements hook_tokens().
 */
function living_spaces_sections_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'group_relationship' && !empty($data[$type])) {
    /** @var \Drupal\group\Entity\GroupRelationshipInterface $group_relationship */
    $group_relationship = $data['group_relationship'];

    foreach ($tokens as $name => $original) {
      if ('living_spaces_section' == $name) {
        if (!empty($group_relationship->section->getValue())) {
          $replacements[$original] = $group_relationship->section->entity->label();
        }
      }
      if ('entity_id_url' == $name) {
        $replacements[$original] = $group_relationship->getEntity()->toUrl()->toString();
      }
    }

    if (!empty($group_relationship->section->getValue()) && $living_spaces_section_tokens = \Drupal::token()->findWithPrefix($tokens, 'living_spaces_section')) {
      $replacements += \Drupal::token()->generate('living_spaces_section', $living_spaces_section_tokens, ['living_spaces_section' => $group_relationship->section->entity], $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
