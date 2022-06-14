<?php

/**
 * @file
 * Hooks specific to the living spaces default module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Invoked when a new space is created from template space.
 *
 * @param \Drupal\group\Entity\GroupInterface $space
 *   New space to be created.
 * @param \Drupal\group\Entity\GroupInterface $template
 *   Space template which was chosen for a new space.
 */
function hook_living_spaces_default_create(\Drupal\group\Entity\GroupInterface $space, \Drupal\group\Entity\GroupInterface $template) {
  $space->set('field_name', NULL);
}

/**
 * @} End of "addtogroup hooks".
 */
