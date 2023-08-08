<?php

namespace Drupal\living_spaces_protected_area\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access area entity.
 *
 * @ContentEntityType(
 *   id = "living_spaces_access_area",
 *   label = @Translation("Access area"),
 *   handlers = {
 *     "access" = "Drupal\living_spaces_protected_area\Access\LivingSpacesAccessAreaAccessControlHandler",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\living_spaces_protected_area\Form\LivingSpacesAccessAreaForm",
 *       "add" = "Drupal\living_spaces_protected_area\Form\LivingSpacesAccessAreaForm",
 *       "edit" = "Drupal\living_spaces_protected_area\Form\LivingSpacesAccessAreaForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "access protected area manage pages",
 *   base_table = "living_spaces_access_area",
 *   data_table = "living_spaces_access_area_field_data",
 *   translatable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "label" = "label",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/living-spaces/protected-area/{living_spaces_access_area}",
 *     "add-page" = "/admin/config/living-spaces/protected-area/add",
 *     "add-form" = "/admin/config/living-spaces/protected-area/add/{living_spaces_access_area_type}",
 *     "edit-form" = "/admin/config/living-spaces/protected-area/{living_spaces_access_area}/edit",
 *     "delete-form" = "/admin/config/living-spaces/protected-area/{living_spaces_access_area}/delete",
 *     "delete-multiple-form" = "/admin/config/living-spaces/protected-area/delete",
 *   },
 *   bundle_entity_type = "living_spaces_access_area_type",
 *   field_ui_base_route = "entity.living_spaces_access_area_type.edit_form",
 *   permission_granularity = "bundle"
 * )
 */
class LivingSpacesProtectedAreaAccessArea extends ContentEntityBase implements LivingSpacesProtectedAreaAccessAreaInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Title'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'))
      ->setDescription(new TranslatableMarkup('The time that the access grant was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'))
      ->setDescription(new TranslatableMarkup('The time that the access grant was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessAreaType() {
    return $this->type->entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromRestrictedPath($path = NULL) {
    $entity_type_manager = \Drupal::entityTypeManager();
    $types = $entity_type_manager->getDefinitions();

    $access_area_manager = $entity_type_manager->getStorage('living_spaces_access_area');
    $path = !empty($path) ? $path : Url::fromRoute('<current>')->toString();

    try {
      foreach (Url::fromUserInput($path)->getRouteParameters() as $parameter => $value) {
        if (isset($types[$parameter]) && $entity = $entity_type_manager->getStorage($parameter)->load($value)) {
          $query = $access_area_manager->getQuery();
          $query->condition('label', $entity->getEntityTypeId() . ':' . $entity->bundle());
          $query->accessCheck(FALSE);

          if ($ids = $query->execute()) {
            $id = reset($ids);
            return $access_area_manager->load($id);
          }
        }
      }
    }
    catch (\Exception $e) {
    }

    $result = NULL;
    if ($areas = \Drupal::cache()->get('living_spaces_protected_area')) {
      foreach ($areas->data as $id => $label) {
        if (\Drupal::service('path.matcher')->matchPath($path, $label)) {
          return $access_area_manager->load($id);
        }
      }
    }
    else {
      $cache = [];
      foreach ($access_area_manager->loadMultiple() as $area) {
        $cache[$area->id()] = $area->label();

        if (\Drupal::service('path.matcher')->matchPath($path, $area->label())) {
          $result = $area;
        }
      }

      \Drupal::cache()->set('living_spaces_protected_area', $cache);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public static function getForUser(AccountInterface $user) {
    if ($area = self::fromRestrictedPath()) {
      $entity_type_manager = \Drupal::entityTypeManager();
      $access_grant_manager = $entity_type_manager->getStorage('living_spaces_access_grant');

      $query = $access_grant_manager->getQuery();
      $query->condition('uid', $user->id());
      $query->condition('access_area', $area->id());
      $query->accessCheck();

      if ($user->id() && $ids = $query->execute()) {
        $id = reset($ids);
        $entity = $access_grant_manager->load($id);

        return [
          'status' => $entity->isPublished(),
          'entity' => $entity,
        ];
      }

      return [
        'status' => FALSE,
        'entity' => $area,
      ];
    }

    return NULL;
  }

}
