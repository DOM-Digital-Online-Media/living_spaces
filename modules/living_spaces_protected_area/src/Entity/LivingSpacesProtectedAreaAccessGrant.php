<?php

namespace Drupal\living_spaces_protected_area\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerTrait;
use Drupal\user\StatusItem;

/**
 * Defines the access grant entity.
 *
 * @ContentEntityType(
 *   id = "living_spaces_access_grant",
 *   label = @Translation("Access grant"),
 *   handlers = {
 *     "access" = "Drupal\living_spaces_protected_area\Access\LivingSpacesAccessGrantAccessControlHandler",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\living_spaces_protected_area\Form\LivingSpacesAccessGrantForm",
 *       "add" = "Drupal\living_spaces_protected_area\Form\LivingSpacesAccessGrantForm",
 *       "edit" = "Drupal\living_spaces_protected_area\Form\LivingSpacesAccessGrantForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "living_spaces_protected_area",
 *   data_table = "living_spaces_protected_area_field_data",
 *   translatable = FALSE,
 *   admin_permission = "access protected area manage pages",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "label",
 *     "owner" = "uid",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/living-spaces/protected-area/grant/{living_spaces_access_grant}",
 *     "add-form" = "/admin/config/living-spaces/protected-area/grant/add",
 *     "edit-form" = "/admin/config/living-spaces/protected-area/grant/{living_spaces_access_grant}/edit",
 *     "delete-form" = "/admin/config/living-spaces/protected-area/grant/{living_spaces_access_grant}/delete",
 *     "delete-multiple-form" = "/admin/config/living-spaces/protected-area/grant/delete",
 *   }
 * )
 */
class LivingSpacesProtectedAreaAccessGrant extends ContentEntityBase implements LivingSpacesProtectedAreaAccessGrantInterface {

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
      ->setLabel(new TranslatableMarkup('Title'));

    $fields['access_area'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Access Area'))
      ->setSetting('target_type', 'living_spaces_access_area')
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
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
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 1,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['browser_key'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Browser fingerprint hash'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['browser_name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Browser name'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['os'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('OS'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 4,
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
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'))
      ->setDescription(new TranslatableMarkup('The time that the access grant was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'))
      ->setDescription(new TranslatableMarkup('The time that the access grant was last edited.'));

    return $fields;
  }

}
