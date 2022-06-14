<?php

namespace Drupal\living_spaces_sections\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\group\Entity\GroupInterface;

/**
 * Provides an interface for living space section entities.
 */
interface LivingSpacesSectionInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Gets a living space section creation timestamp.
   *
   * @return int
   *   Creation timestamp of a space section.
   */
  public function getCreatedTime();

  /**
   * Sets a living space section creation timestamp.
   *
   * @param int $timestamp
   *   Creation timestamp of a space section.
   *
   * @return $this
   *   Called living space section entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets a living space section tab label.
   *
   * @return string
   *   Returns tab label for a section.
   */
  public function getTabLabel();

  /**
   * Sets a living space section tab label.
   *
   * @param string $tab_label
   *   Tab label to use for a section on group.
   *
   * @return $this
   *   Called living space section entity.
   */
  public function setTabLabel($tab_label);

  /**
   * Returns inner tab label. If it's not set regular tab label returned instead.
   *
   * @return string
   *   Inner tab label.
   */
  public function getInnerTabLabel();

  /**
   * Gets a section path on a group. Section will be available on a group.
   *
   * @return string
   *   Returns section path on a group.
   */
  public function getPath();

  /**
   * Sets a living space section path.
   *
   * @param string $path
   *   Section tab path on a space.
   *
   * @return $this
   *   Called living space section entity.
   */
  public function setPath($path);

  /**
   * Returns section type class for a section.
   *
   * @return \Drupal\living_spaces_sections\Entity\LivingSpacesSectionTypeInterface
   *   Section type entity.
   */
  public function getSectionType();

  /**
   * FOR INTERNAL USE ONLY.
   *
   * Sets internal group entity for a section entity. Useful for cases when
   * section just created and needs to be validated, therefore related group's
   * field is not yet saved, so we don't have relation through it.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group entity.
   *
   * @return $this
   *   Section entity.
   */
  public function setGroup(GroupInterface $group);

  /**
   * Returns group entity for which section instance was created.
   *
   * @return \Drupal\group\Entity\GroupInterface|null
   *   Group entity.
   */
  public function getGroup();

  /**
   * Returns link to a group for which section instance was created.
   *
   * @return \Drupal\Core\Link|null
   *   Group link.
   */
  public function getGroupLink();

  /**
   * FOR INTERNAL USE ONLY.
   *
   * Sets internal parent section for a section entity. Useful for cases when
   * section just created and needs to be validated, therefore parent section
   * field is not yet saved, so we don't have relation through it.
   *
   * @param \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface $section
   *   Section entity.
   *
   * @return $this
   *   Section entity.
   */
  public function setParentSection(LivingSpacesSectionInterface $section);

  /**
   * Returns parent section entity if current section is a sub-section.
   *
   * @return \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface|null
   *   Parent section entity if exists.
   */
  public function getParentSection();

  /**
   * Returns parent section type entity if current section is a sub-section.
   *
   * @return \Drupal\living_spaces_sections\Entity\LivingSpacesSectionTypeInterface|null
   *   Parent section type for entity if exists.
   */
  public function getParentSectionType();

  /**
   * Returns sub-section entities for a section.
   *
   * @return \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface[]
   *   Sub-sections of a section.
   */
  public function getChildrenSections();

  /**
   * Returns sub-section types available for a section.
   *
   * @return \Drupal\living_spaces_sections\Entity\LivingSpacesSectionTypeInterface[]
   *   Sub-section types available for a section.
   */
  public function getChildrenSectionTypes();

  /**
   * Returns true if section has sub-sections available.
   *
   * @return bool
   *   TRUE if a section has sub-sections, FALSE otherwise.
   */
  public function isParent();

  /**
   * Returns true if section is a sub-section i.e. has parent section.
   *
   * @return bool
   *   TRUE if a section is a sub-section, FALSE otherwise.
   */
  public function isChild();

  /**
   * Returns action links for a section. Those are shows on a section view as a block.
   *
   * @return array
   *   Array of links suitable for "operations" render element.
   */
  public function getSectionActions();

  /**
   * Callback which called when section is cloned before entity reference fields are cloned.
   *
   * @param \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface $clone
   *   Cloned section.
   * @param \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface $original
   *   Original section from which clone was created.
   */
  public static function preCloneCallback(LivingSpacesSectionInterface $clone, LivingSpacesSectionInterface $original);

  /**
   * Callback which called when section is cloned after entity reference fields are cloned.
   *
   * @param \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface $clone
   *   Cloned section.
   * @param \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface $original
   *   Original section from which clone was created.
   *
   * @return bool
   *   Return TRUE if callback performed any changes on $clone and it needs to
   *   be saved to apply the changes.
   */
  public static function postCloneCallback(LivingSpacesSectionInterface $clone, LivingSpacesSectionInterface $original);

}
