<?php

namespace Drupal\living_spaces_protected_area\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the access area type entity.
 *
 * @ConfigEntityType(
 *   id = "living_spaces_access_area_type",
 *   label = @Translation("Access area type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\living_spaces_protected_area\LivingSpacesAccessAreaTypeBuilder",
 *     "form" = {
 *       "default" = "Drupal\living_spaces_protected_area\Form\LivingSpacesAccessAreaTypeForm",
 *       "add" = "Drupal\living_spaces_protected_area\Form\LivingSpacesAccessAreaTypeForm",
 *       "edit" = "Drupal\living_spaces_protected_area\Form\LivingSpacesAccessAreaTypeForm",
 *       "delete" = "Drupal\living_spaces_protected_area\Form\LivingSpacesAccessAreaTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     },
 *   },
 *   config_prefix = "living_spaces_access_area_type",
 *   admin_permission = "access protected area manage pages",
 *   bundle_of = "living_spaces_access_area",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/living-spaces/protected-area/type/{living_spaces_access_area_type}",
 *     "collection" = "/admin/config/living-spaces/protected-area/type",
 *     "add-form" = "/admin/config/living-spaces/protected-area/type/add",
 *     "edit-form" = "/admin/config/living-spaces/protected-area/type/{living_spaces_access_area_type}/edit",
 *     "delete-form" = "/admin/config/living-spaces/protected-area/type/{living_spaces_access_area_type}/delete",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description"
 *   }
 * )
 */
class LivingSpacesProtectedAreaAccessAreaType extends ConfigEntityBundleBase implements LivingSpacesProtectedAreaAccessAreaTypeInterface {

  /**
   * The machine name of the access area type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the access area type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of the access area type.
   *
   * @var string
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

}
