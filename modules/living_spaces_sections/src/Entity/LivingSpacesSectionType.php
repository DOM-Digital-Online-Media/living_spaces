<?php

namespace Drupal\living_spaces_sections\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the living spaces section type entity i.e. section bundles.
 *
 * @ConfigEntityType(
 *   id = "living_spaces_section_type",
 *   label = @Translation("Living spaces section type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\living_spaces_sections\LivingSpacesSectionTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\living_spaces_sections\Form\LivingSpacesSectionTypeForm",
 *       "edit" = "Drupal\living_spaces_sections\Form\LivingSpacesSectionTypeForm",
 *       "delete" = "Drupal\living_spaces_sections\Form\LivingSpacesSectionTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "living_spaces_section_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "living_spaces_section",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/living-spaces-section-type/{living_spaces_section_type}",
 *     "add-form" = "/admin/structure/living-spaces-section-type/add",
 *     "edit-form" = "/admin/structure/living-spaces-section-type/{living_spaces_section_type}/edit",
 *     "delete-form" = "/admin/structure/living-spaces-section-type/{living_spaces_section_type}/delete",
 *     "collection" = "/admin/structure/living-spaces-section-types"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "parent"
 *   }
 * )
 */
class LivingSpacesSectionType extends ConfigEntityBundleBase implements LivingSpacesSectionTypeInterface {

  /**
   * Living space section type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * Living space section type label.
   *
   * @var string
   */
  protected $label;

  /**
   * Living space section type parent. Empty if section does not have parent.
   *
   * @var string|null
   */
  protected $parent;

  /**
   * {@inheritdoc}
   */
  public function getParent() {
    return $this->parent;
  }

}
