<?php

namespace Drupal\living_spaces_sections;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\living_spaces_sections\Entity\LivingSpacesSectionType;

/**
 * Provides dynamic permissions for living_spaces_sections module.
 */
class LivingSpacesSectionsPermissions {
  use StringTranslationTrait;

  /**
   * Returns dynamic permissions per section type.
   */
  public function sectionTypePermissions() {
    $permissions = [];
    $types = LivingSpacesSectionType::loadMultiple();

    foreach ($types as $type) {
      $permissions["view {$type->id()} section of a living space"] = [
        'title' => $this->t('View %name section of a living space', [
          '%name' => $type->label(),
        ])->__toString(),
      ];
    }
    return $permissions;
  }

}
