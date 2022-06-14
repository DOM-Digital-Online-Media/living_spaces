<?php

namespace Drupal\living_spaces_protected_area\Access;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides dynamic permissions for space access grants.
 */
class LivingSpacesProtectedAreaAccessGrantPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of space access grant permissions.
   *
   * @return array
   *   The space access grant permissions.
   */
  public function accessGrantPermissions() {
    return [
      'view access grant' => [
        'title' => $this->t('View access grant'),
      ],
      'view unpublished access grant' => [
        'title' => $this->t('View unpublished access grant'),
      ],
      'edit access grant' => [
        'title' => $this->t('Edit access grant'),
      ],
      'delete access grant' => [
        'title' => $this->t('Delete access grant'),
      ],
    ];
  }

}
