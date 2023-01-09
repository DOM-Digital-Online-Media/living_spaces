<?php

namespace Drupal\living_spaces_intranet\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the ban type entity.
 *
 * @ConfigEntityType(
 *   id = "living_spaces_ban_type",
 *   label = @Translation("Ban type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\living_spaces_intranet\LivingSpacesBanTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\living_spaces_intranet\Form\LivingSpacesBanTypeForm",
 *       "add" = "Drupal\living_spaces_intranet\Form\LivingSpacesBanTypeForm",
 *       "edit" = "Drupal\living_spaces_intranet\Form\LivingSpacesBanTypeForm",
 *       "delete" = "Drupal\living_spaces_intranet\Form\LivingSpacesBanTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     },
 *   },
 *   config_prefix = "living_spaces_ban_type",
 *   admin_permission = "administer ban",
 *   bundle_of = "living_spaces_ban",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/living-spaces-ban-type/{living_spaces_ban_type}",
 *     "collection" = "/admin/structure/living-spaces-ban-type",
 *     "add-form" = "/admin/structure/living-spaces-ban-type/add",
 *     "edit-form" = "/admin/structure/living-spaces-ban-type/{living_spaces_ban_type}/edit",
 *     "delete-form" = "/admin/structure/living-spaces-ban-type/{living_spaces_ban_type}/delete",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description"
 *   }
 * )
 */
class LivingSpacesBanType extends ConfigEntityBundleBase implements LivingSpacesBanTypeInterface {

  /**
   * The machine name of the ban type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the ban type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of the ban type.
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
