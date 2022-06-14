<?php

namespace Drupal\living_spaces_event\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the space event invite entity.
 *
 * @ContentEntityType(
 *   id = "living_spaces_event_invite",
 *   label = @Translation("Space event invite"),
 *   handlers = {
 *     "access" = "Drupal\living_spaces_event\Access\LivingSpaceEventInviteAccessControlHandler",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\living_spaces_event\Form\LivingSpaceEventInviteForm",
 *       "add" = "Drupal\living_spaces_event\Form\LivingSpaceEventInviteForm",
 *       "edit" = "Drupal\living_spaces_event\Form\LivingSpaceEventInviteForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "living_spaces_event_invite",
 *   data_table = "living_spaces_event_invite_field_data",
 *   translatable = FALSE,
 *   admin_permission = "administer living spaces event invite",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/living-spaces-event-invite/{living_spaces_event_invite}",
 *     "add-form" = "/living-spaces-event-invite/add",
 *     "edit-form" = "/living-spaces-event-invite/{living_spaces_event_invite}/edit",
 *     "delete-form" = "/living-spaces-event-invite/{living_spaces_event_invite}/delete",
 *     "delete-multiple-form" = "/living-spaces-event-invite/delete",
 *   }
 * )
 */
class LivingSpaceEventInvite extends ContentEntityBase implements LivingSpaceEventInviteInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['event'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Event'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'living_spaces_event')
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['uid']
      ->setLabel(new TranslatableMarkup('User'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 1,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['status'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Status'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['event_status' => 'event_status']])
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label',
        'weight' => 2,
        'settings' => [
          'link' => FALSE,
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
