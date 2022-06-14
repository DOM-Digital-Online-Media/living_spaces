<?php

namespace Drupal\living_spaces_event\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the spaces event type entity.
 *
 * @ConfigEntityType(
 *   id = "living_spaces_event_type",
 *   label = @Translation("Spaces event type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\living_spaces_event\LivingSpaceEventTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\living_spaces_event\Form\LivingSpaceEventTypeForm",
 *       "add" = "Drupal\living_spaces_event\Form\LivingSpaceEventTypeForm",
 *       "edit" = "Drupal\living_spaces_event\Form\LivingSpaceEventTypeForm",
 *       "delete" = "Drupal\living_spaces_event\Form\LivingSpaceEventTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     },
 *   },
 *   config_prefix = "living_spaces_event_type",
 *   admin_permission = "administer living spaces event",
 *   bundle_of = "living_spaces_event",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/living-spaces-event-type/{living_spaces_event_type}",
 *     "collection" = "/admin/structure/living-spaces-event-type",
 *     "add-form" = "/admin/structure/living-spaces-event-type/add",
 *     "edit-form" = "/admin/structure/living-spaces-event-type/{living_spaces_event_type}/edit",
 *     "delete-form" = "/admin/structure/living-spaces-event-type/{living_spaces_event_type}/delete",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description"
 *   }
 * )
 */
class LivingSpaceEventType extends ConfigEntityBundleBase implements LivingSpaceEventTypeInterface {

  /**
   * The machine name of the space event type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the space event type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of the space event type.
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
