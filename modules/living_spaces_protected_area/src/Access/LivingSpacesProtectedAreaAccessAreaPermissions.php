<?php

namespace Drupal\living_spaces_protected_area\Access;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\living_spaces_protected_area\Entity\LivingSpacesProtectedAreaAccessAreaType;

/**
 * Provides dynamic permissions for access area of different types.
 */
class LivingSpacesProtectedAreaAccessAreaPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of access area type permissions.
   *
   * @return array
   *   The access area permissions.
   */
  public function accessAreaPermissions() {
    $perms = [];

    foreach (LivingSpacesProtectedAreaAccessAreaType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }

    return $perms;
  }

  /**
   * Returns a list of access area permissions for a given access area type.
   *
   * @param \Drupal\living_spaces_protected_area\Entity\LivingSpacesProtectedAreaAccessAreaType $type
   *   The access area type.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildPermissions(LivingSpacesProtectedAreaAccessAreaType $type) {
    $params = ['%type_name' => $type->label()];

    return [
      "create {$type->id()} access area" => [
        'title' => $this->t('%type_name: Create new access area', $params),
      ],
      "view {$type->id()} access area" => [
        'title' => $this->t('%type_name: View access area', $params),
      ],
      "edit {$type->id()} access area" => [
        'title' => $this->t('%type_name: Edit access area', $params),
      ],
      "delete {$type->id()} access area" => [
        'title' => $this->t('%type_name: Delete access area', $params),
      ],
    ];
  }

}
