<?php

/**
 * @file
 * Hooks specific to the Living spaces sections module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Provides living space group section entity for given group relationship.
 *
 * @param \Drupal\group\Entity\GroupRelationshipInterface $relationship
 *   Living space group relationship entity.
 * @param \Drupal\living_spaces_sections\Entity\LivingSpacesSection|null $section
 *   Living space group section entity.
 */
function hook_living_spaces_sections_content_alter(\Drupal\group\Entity\GroupRelationshipInterface $relationship, &$section) {
  $section = \Drupal\living_spaces_sections\Entity\LivingSpacesSection::load(1);
}

/**
 * @} End of "addtogroup hooks".
 */
