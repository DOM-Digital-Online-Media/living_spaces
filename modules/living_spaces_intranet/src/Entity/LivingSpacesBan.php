<?php

namespace Drupal\living_spaces_intranet\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the ban entity.
 *
 * @ContentEntityType(
 *   id = "living_spaces_ban",
 *   label = @Translation("Ban"),
 *   handlers = {
 *     "access" = "Drupal\living_spaces_intranet\Access\LivingSpacesBanAccessControlHandler",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\living_spaces_intranet\LivingSpacesBanListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\living_spaces_intranet\Form\LivingSpacesBanForm",
 *       "add" = "Drupal\living_spaces_intranet\Form\LivingSpacesBanForm",
 *       "edit" = "Drupal\living_spaces_intranet\Form\LivingSpacesBanForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "living_spaces_ban",
 *   data_table = "living_spaces_ban_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer ban",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "label" = "label",
 *     "owner" = "uid",
 *     "langcode" = "langcode"
 *   },
 *   links = {
 *     "canonical" = "/living-spaces-ban/{living_spaces_ban}",
 *     "collection" = "/admin/content/living-spaces-ban",
 *     "add-page" = "/living-spaces-ban/add",
 *     "add-form" = "/living-spaces-ban/add/{living_spaces_ban_type}",
 *     "edit-form" = "/living-spaces-ban/{living_spaces_ban}/edit",
 *     "delete-form" = "/living-spaces-ban/{living_spaces_ban}/delete",
 *     "delete-multiple-form" = "/living-spaces-ban/delete",
 *   },
 *   bundle_entity_type = "living_spaces_ban_type",
 *   field_ui_base_route = "entity.living_spaces_ban_type.edit_form",
 *   permission_granularity = "bundle"
 * )
 */
class LivingSpacesBan extends ContentEntityBase implements LivingSpacesBanInterface {

  use EntityOwnerTrait;
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Ban reason'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -100,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -100,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid']
      ->setLabel(new TranslatableMarkup('User'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => -99,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['target_user'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Banned user'))
      ->setSetting('target_type', 'user')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => -98,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'author',
        'weight' => -98,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['expire'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('Expire'))
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => -97,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'timestamp',
        'weight' => -97,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'))
      ->setDescription(new TranslatableMarkup('The time that the ban was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'))
      ->setDescription(new TranslatableMarkup('The time that the ban was last edited.'));

    return $fields;
  }

}
