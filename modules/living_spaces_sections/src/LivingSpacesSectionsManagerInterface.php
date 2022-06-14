<?php

namespace Drupal\living_spaces_sections;

use Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface;
use Drupal\group\Entity\GroupInterface;

/**
 * Interface for section manager service.
 */
interface LivingSpacesSectionsManagerInterface {

  /**
   * Returns whether group type has sections functionality enabled.
   *
   * @param string $group_type
   *   Group entity bundle.
   *
   * @return bool
   *   True if sections enabled for the group bundle.
   */
  public function isSectionsEnabled(string $group_type);

  /**
   * Returns sections enabled for the group.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group entity.
   *
   * @return \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface[]
   *   Array of enabled sections for the group.
   */
  public function getSectionsFromGroup(GroupInterface $group);

  /**
   * Returns sub-sections enabled for the parent section.
   *
   * @param \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface $section
   *   Parent section entity.
   *
   * @return \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface[]
   *   Array of enabled sub-sections for the section.
   */
  public function getSubSectionsFromSection(LivingSpacesSectionInterface $section);

  /**
   * Returns section entity for given group and section type if it exists.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Living space group entity.
   * @param string $type
   *   Section bundle name.
   *
   * @return \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface|null
   *   Section entity.
   */
  public function getSectionFromGroupByType(GroupInterface $group, $type);

  /**
   * Returns section entity for given group and section path if it exists.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Living space group entity.
   * @param string $path
   *   Section path. Either [section] or [section]/[section].
   *
   * @return \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface|null
   *   Section entity.
   */
  public function getSectionFromGroupByPath(GroupInterface $group, $path);

  /**
   * Return section type entity.
   *
   * @param string $section_type
   *   Section type id.
   *
   * @return \Drupal\living_spaces_sections\Entity\LivingSpacesSectionTypeInterface|null
   *   Section type entity.
   */
  public function getSectionTypeEntity($section_type);

}
