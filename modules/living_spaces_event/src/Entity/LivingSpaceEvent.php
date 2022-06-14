<?php

namespace Drupal\living_spaces_event\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\user\StatusItem;

/**
 * Defines the space event entity.
 *
 * @ContentEntityType(
 *   id = "living_spaces_event",
 *   label = @Translation("Space event"),
 *   handlers = {
 *     "access" = "Drupal\living_spaces_event\Access\LivingSpaceEventAccessControlHandler",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\living_spaces_event\LivingSpaceEventListBuilder",
 *     "views_data" = "Drupal\living_spaces_event\LivingSpaceEventViewsData",
 *     "form" = {
 *       "default" = "Drupal\living_spaces_event\Form\LivingSpaceEventForm",
 *       "add" = "Drupal\living_spaces_event\Form\LivingSpaceEventForm",
 *       "edit" = "Drupal\living_spaces_event\Form\LivingSpaceEventForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer living spaces event",
 *   base_table = "living_spaces_event",
 *   data_table = "living_spaces_event_field_data",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *     "label" = "label",
 *     "published" = "status",
 *     "langcode" = "langcode"
 *   },
 *   links = {
 *     "canonical" = "/living-spaces-event/{living_spaces_event}",
 *     "collection" = "/admin/content/living-spaces-event",
 *     "add-page" = "/living-spaces-event/add",
 *     "add-form" = "/living-spaces-event/add/{living_spaces_event_type}",
 *     "edit-form" = "/living-spaces-event/{living_spaces_event}/edit",
 *     "delete-form" = "/living-spaces-event/{living_spaces_event}/delete"
 *   },
 *   bundle_entity_type = "living_spaces_event_type",
 *   field_ui_base_route = "entity.living_spaces_event_type.edit_form",
 *   permission_granularity = "bundle"
 * )
 */
class LivingSpaceEvent extends ContentEntityBase implements LivingSpaceEventInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['status']->getItemDefinition()->setClass(StatusItem::class);
    $fields['status']
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 99,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['uid']
      ->setLabel(t('Author'))
      ->setDescription(t('The username of the event creator.'))
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the event was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the event was last edited.'));

    if (\Drupal::moduleHandler()->moduleExists('path')) {
      $fields['path'] = BaseFieldDefinition::create('path')
        ->setLabel(t('URL alias'))
        ->setTranslatable(TRUE)
        ->setDisplayOptions('form', [
          'type' => 'path',
          'weight' => 100,
        ])
        ->setDisplayConfigurable('form', TRUE)
        ->setComputed(TRUE);
    }

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getEventType() {
    return $this->type->entity;
  }

}
