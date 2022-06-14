<?php

namespace Drupal\living_spaces_event\Access;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\living_spaces_event\Entity\LivingSpaceEventType;

/**
 * Provides dynamic permissions for space events of different types.
 */
class LivingSpacesEventPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of space event type permissions.
   *
   * @return array
   *   The space type permissions.
   */
  public function eventPermissions() {
    $perms = [];

    foreach (LivingSpaceEventType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }

    return $perms;
  }

  /**
   * Returns a list of space event permissions for a given space event type.
   *
   * @param \Drupal\living_spaces_event\Entity\LivingSpaceEventType $type
   *   The space event type.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildPermissions(LivingSpaceEventType $type) {
    $params = ['%type_name' => $type->label()];

    return [
      "create {$type->id()} space event" => [
        'title' => $this->t('%type_name: Create new space event', $params),
      ],
      "view {$type->id()} space event" => [
        'title' => $this->t('%type_name: View space event', $params),
      ],
      "view any unpublished {$type->id()} space event" => [
        'title' => $this->t('%type_name: View any unpublished space event', $params),
      ],
      "view own unpublished {$type->id()} space event" => [
        'title' => $this->t('%type_name: View own unpublished space event', $params),
      ],
      "edit {$type->id()} space event" => [
        'title' => $this->t('%type_name: Edit space event', $params),
      ],
      "delete {$type->id()} space event" => [
        'title' => $this->t('%type_name: Delete space event', $params),
      ],
    ];
  }

}
