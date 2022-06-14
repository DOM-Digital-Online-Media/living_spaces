<?php

namespace Drupal\living_spaces_sections\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining living space section type entities.
 */
interface LivingSpacesSectionTypeInterface extends ConfigEntityInterface {

  /**
   * Returns parent living space section type ID if a section type has parent.
   *
   * @return null|string
   *   Parent section.
   */
  public function getParent();

}
