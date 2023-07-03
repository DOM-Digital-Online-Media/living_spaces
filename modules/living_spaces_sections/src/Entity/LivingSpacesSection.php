<?php

namespace Drupal\living_spaces_sections\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\Query\QueryException;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\living_spaces_sections\LivingSpacesSectionsHtmlRouteProvider;
use Drupal\group\Entity\GroupInterface;

/**
 * Defines the living space section entity.
 *
 * @ContentEntityType(
 *   id = "living_spaces_section",
 *   label = @Translation("Living space section"),
 *   bundle_label = @Translation("Living space section type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "form" = {
 *       "default" = "Drupal\living_spaces_sections\Form\LivingSpacesSectionForm",
 *       "add" = "Drupal\living_spaces_sections\Form\LivingSpacesSectionForm",
 *       "edit" = "Drupal\living_spaces_sections\Form\LivingSpacesSectionForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\living_spaces_sections\LivingSpacesSectionsHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\living_spaces_sections\LivingSpacesSectionsAccessControlHandler",
 *   },
 *   base_table = "living_spaces_section",
 *   translatable = FALSE,
 *   admin_permission = "administer living spaces sections settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *   },
 *   uri_callback = "Drupal\living_spaces_sections\Entity\LivingSpacesSection::buildUri",
 *   bundle_entity_type = "living_spaces_section_type",
 *   field_ui_base_route = "entity.living_spaces_section_type.edit_form",
 *   links = {
 *     "canonical" = "/admin/structure/living-spaces-section/{living_spaces_section}"
 *   }
 * )
 */
class LivingSpacesSection extends ContentEntityBase implements LivingSpacesSectionInterface {

  use EntityChangedTrait;
  use StringTranslationTrait;

  /**
   * Group entity of a section.
   *
   * @var \Drupal\group\Entity\GroupInterface|null
   */
  private $group = NULL;

  /**
   * Parent section of a sub-section.
   *
   * @var \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface|null
   */
  private $parentSection = NULL;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['tab'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tab label'))
      ->setDescription(t('Label to be used for tab on a group.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
      ]);

    $fields['path'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Path'))
      ->setDescription(t('Path for section on a group page.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
      ])
      ->setConstraints(['LivingSpacesSectionsPathConstraint' => []]);

    $fields['inner_tab'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Inner tab label'))
      ->setDescription(t('Label for a second level tab on parent sections.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the section was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the section was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function setGroup(GroupInterface $group) {
    $this->group = $group;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup() {
    // Return internal group only if it's set.
    if ($this->group instanceof GroupInterface) {
      return $this->group;
    }

    $id = $this->id();
    if ($parent = $this->getParentSection()) {
      $id = $parent->id();
    }

    if (!$id) {
      return NULL;
    }

    // Look for group that have reference to parent or to current section.
    $group_manager = \Drupal::entityTypeManager()->getStorage('group');
    $query = $group_manager->getQuery();
    $query->condition(LIVING_SPACES_SECTIONS_FIELD, $id);
    $query->accessCheck(FALSE);
    $gids = $query->execute();

    return !empty($gids) ? $group_manager->load(reset($gids)) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupLink() {
    return ($group = $this->getGroup())
      ? $group->toLink()
      : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionActions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function setParentSection(LivingSpacesSectionInterface $section) {
    $this->parentSection = $section;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentSection() {
    if ($this->parentSection instanceof LivingSpacesSectionInterface) {
      return $this->parentSection;
    }

    try {
      $entity_storage = \Drupal::entityTypeManager()
        ->getStorage('living_spaces_section');
      $query = $entity_storage->getQuery()
        ->condition(LIVING_SPACES_SECTIONS_FIELD, $this->id())
        ->execute();

      return !empty($query)
        ? $entity_storage->load(reset($query))
        : NULL;
    }
    catch (QueryException $exception) {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getParentSectionType() {
    /** @var \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface $manager */
    $manager = \Drupal::service('living_spaces_sections.manager');
    $parent = $this->getSectionType()->getParent();
    return $parent ? $manager->getSectionTypeEntity($parent) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getChildrenSections() {
    /** @var \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface $manager */
    $manager = \Drupal::service('living_spaces_sections.manager');
    return $manager->getSubSectionsFromSection($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getChildrenSectionTypes() {
    return $this->entityTypeManager()
      ->getStorage($this->getEntityType()->getBundleEntityType())
      ->loadByProperties(['parent' => $this->bundle()]);
  }

  /**
   * {@inheritdoc}
   */
  public function isParent() {
    return count($this->getChildrenSectionTypes()) > 0;
  }

  /**
   * {@inheritdoc}
   */
  public function isChild() {
    return (boolean) $this->getParentSectionType();
  }

  /**
   * Return bundle label as a section label.
   */
  public function label() {
    return $this->type->entity->label();
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTabLabel() {
    $value = $this->get('tab')->getString();
    $label = empty($value) ? $this->label() : $value;
    // Used t() to enable translation.
    // phpcs:ignore
    return $this->t($label);
  }

  /**
   * {@inheritdoc}
   */
  public function setTabLabel($tab_label) {
    $this->set('tab', $tab_label);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getInnerTabLabel() {
    $value = $this->get('inner_tab')->getString();
    return empty($value) ? $this->getTabLabel() : $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return $this->get('path')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function setPath($path) {
    $this->set('path', $path);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionType() {
    /** @var \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface $manager */
    $manager = \Drupal::service('living_spaces_sections.manager');
    return $manager->getSectionTypeEntity($this->bundle());
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    parent::delete();

    // Take either parent section or group to remove reference from.
    $entity = $this->getParentSection();
    $entity = $entity ?? $this->getGroup();

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    if ($entity && $entity->hasField(LIVING_SPACES_SECTIONS_FIELD)) {
      $values = $entity->get(LIVING_SPACES_SECTIONS_FIELD)->getValue();
      foreach ($values as $delta => $value) {
        if ($value['target_id'] === $this->id()) {
          unset($values[$delta]);
        }
      }
      $entity->set(LIVING_SPACES_SECTIONS_FIELD, array_values($values))->save();
    }

    // If we removed parent section we should remove children as well.
    if ($this->isParent()) {
      foreach ($this->getChildrenSections() as $section) {
        $section->delete();
      }
    }
  }

  /**
   * Returns whether or not the entity is published.
   *
   * @return bool
   *   TRUE if the entity is published, FALSE otherwise.
   */
  public function isPublished() {
    // We don't need status for living space section.
    return TRUE;
  }

  /**
   * Sets the entity as published.
   *
   * @return $this
   *
   * @see \Drupal\Core\Entity\EntityPublishedInterface::setUnpublished()
   */
  public function setPublished() {
    // We don't need status for living space section.
    return $this;
  }

  /**
   * Sets the entity as unpublished.
   *
   * @return $this
   */
  public function setUnpublished() {
    // We don't need status for living space section.
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function getListCacheTagsToInvalidate() {
    $tags = [];
    if ($space = $this->getGroup()) {
      $tags = $space->getCacheTags();
    }
    return Cache::mergeTags($tags, parent::getListCacheTagsToInvalidate());
  }

  /**
   * {@inheritdoc}
   */
  public function toUrl($rel = 'canonical', array $options = []) {
    return self::buildUri($this);
  }

  /**
   * Callback for section url which depends on whether section is sub-section.
   *
   * @param \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface $section
   *   Section entity.
   *
   * @return \Drupal\Core\Url
   *   URL of section.
   */
  public static function buildUri(LivingSpacesSectionInterface $section) {
    if ($group = $section->getGroup()) {
      $route_parameters = [
        'group' => $group->id(),
      ];

      if ($section->isChild()) {
        $route = LivingSpacesSectionsHtmlRouteProvider::SUB_SECTION_ROUTE;
        $route_parameters['section'] = $section->getParentSection()->getPath();
        $route_parameters['sub_section'] = $section->getPath();
      }
      else {
        $route = LivingSpacesSectionsHtmlRouteProvider::SECTION_ROUTE;
        $route_parameters['section'] = $section->getPath();
      }
      return Url::fromRoute($route, $route_parameters);
    }
    return Url::fromRoute('<front>');
  }

  /**
   * {@inheritdoc}
   */
  public static function preCloneCallback(LivingSpacesSectionInterface $clone, LivingSpacesSectionInterface $original) {
    // Nothing for base class.
  }

  /**
   * {@inheritdoc}
   */
  public static function postCloneCallback(LivingSpacesSectionInterface $clone, LivingSpacesSectionInterface $original) {
    // Nothing for base class.
    return FALSE;
  }

}
