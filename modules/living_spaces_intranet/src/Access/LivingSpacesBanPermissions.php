<?php

namespace Drupal\living_spaces_intranet\Access;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\living_spaces_intranet\Entity\LivingSpacesBanType;

/**
 * Provides dynamic permissions for bans of different types.
 */
class LivingSpacesBanPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of ban type permissions.
   *
   * @return array
   *   Ban permissions.
   */
  public function banPermissions() {
    $perms = [];

    foreach (LivingSpacesBanType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }

    return $perms;
  }

  /**
   * Returns a list of ban permissions for a given ban type.
   *
   * @param \Drupal\living_spaces_intranet\Entity\LivingSpacesBanType $type
   *   The ban type.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildPermissions(LivingSpacesBanType $type) {
    $params = ['%type_name' => $type->label()];

    return [
      "create {$type->id()} ban" => [
        'title' => $this->t('%type_name: Create new ban', $params),
      ],
      "view {$type->id()} ban" => [
        'title' => $this->t('%type_name: View ban', $params),
      ],
      "edit {$type->id()} ban" => [
        'title' => $this->t('%type_name: Edit ban', $params),
      ],
      "delete {$type->id()} ban" => [
        'title' => $this->t('%type_name: Delete ban', $params),
      ],
    ];
  }

}
