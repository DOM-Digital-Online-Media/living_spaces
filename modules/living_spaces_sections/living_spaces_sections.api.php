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
 * Provides living space group section entity for given group content.
 *
 * @param \Drupal\group\Entity\GroupContentInterface $content
 *   Living space group content entity.
 * @param \Drupal\living_spaces_sections\Entity\LivingSpacesSection|null $section
 *   Living space group section entity.
 */
function hook_living_spaces_sections_content_alter(\Drupal\group\Entity\GroupContentInterface $content, &$section) {
  $section = \Drupal\living_spaces_sections\Entity\LivingSpacesSection::load(1);
}

/**
 * @} End of "addtogroup hooks".
 */
