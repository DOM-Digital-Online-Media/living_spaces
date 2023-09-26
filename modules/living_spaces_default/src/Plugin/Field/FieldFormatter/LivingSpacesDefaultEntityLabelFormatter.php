<?php

namespace Drupal\living_spaces_default\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;
use Drupal\group\Entity\GroupInterface;

/**
 * Plugin implementation of the 'entity reference label' formatter.
 *
 * @FieldFormatter(
 *   id = "living_spaces_default_entity_label",
 *   label = @Translation("Label (exclude default spaces)"),
 *   description = @Translation("Display the label of the referenced entities with exculding default spaces."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class LivingSpacesDefaultEntityLabelFormatter extends EntityReferenceLabelFormatter {

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode) {
    $entities = parent::getEntitiesToView($items, $langcode);

    foreach ($entities as $delta => $entity) {
      if ($entity instanceof GroupInterface && $entity->get('is_default')->getString()) {
        unset($entities[$delta]);
      }
    }

    return array_values($entities);
  }

}
