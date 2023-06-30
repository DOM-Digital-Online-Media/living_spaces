<?php

namespace Drupal\living_spaces_sections\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface;

/**
 * Provides 'section' field info.
 *
 * @internal
 */
class LivingSpacesSectionsSectionStorage extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    /** @var \Drupal\group\Entity\GroupRelationshipInterface $group_relationship */
    $group_relationship = $this->getEntity();

    $section = NULL;
    \Drupal::moduleHandler()->alter('living_spaces_sections_content', $group_relationship, $section);
    if ($section instanceof LivingSpacesSectionInterface) {
      $this->list[0] = $this->createItem(0, $section->id());
    }
  }

}
