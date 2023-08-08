<?php

namespace Drupal\living_spaces_sections;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface;
use Drupal\group\Entity\GroupInterface;

/**
 * Manager for section related methods.
 */
class LivingSpacesSectionsManager implements LivingSpacesSectionsManagerInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * LivingSpacesSectionsManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function isSectionsEnabled(string $group_type) {
    $types = $this->configFactory->get('living_spaces_sections.settings')
      ->get('group_types');
    $types = is_array($types) ? $types : [];
    return in_array($group_type, $types);
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionsFromGroup(GroupInterface $group) {
    return $group->get(LIVING_SPACES_SECTIONS_FIELD)->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getSubSectionsFromSection(LivingSpacesSectionInterface $section) {
    if ($section->isParent()) {
      return $section->get(LIVING_SPACES_SECTIONS_FIELD)->referencedEntities();
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionFromGroupByType(GroupInterface $group, $type) {
    $data = NULL;

    if ($section_type_entity = $this->getSectionTypeEntity($type)) {
      // If section is parent, retrieve from group section field.
      $sections_entity = $group;

      // If section has parent, retrieve it from the group and check its children.
      if ($parent = $section_type_entity->getParent()) {
        if ($parent = $this->getSectionFromGroupByType($group, $parent)) {
          $sections_entity = $parent;
        }
      }

      $sections = array_column($sections_entity->get(LIVING_SPACES_SECTIONS_FIELD)->getValue(), 'target_id');
      if (!empty($sections)) {
        $storage = $this->entityTypeManager->getStorage('living_spaces_section');
        $result = $storage->getQuery()
          ->condition('id', $sections, 'IN')
          ->condition('type', $type)
          ->accessCheck()
          ->execute();

        if (!empty($result)) {
          /** @var \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface $data */
          $data = $storage->load(reset($result));
        }
      }
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionFromGroupByPath(GroupInterface $group, $path) {
    $data = NULL;
    $path = explode('/', $path);
    $section = $path[0] ?? NULL;
    $sub_section = $path[1] ?? NULL;

    // Fetch ids of group's sections.
    $group_sections = array_column(
      $group->get(LIVING_SPACES_SECTIONS_FIELD)->getValue(),
      'target_id'
    );

    if ($section && $group_sections) {
      // Load section by configured path, if we don't have it throw exception.
      $section = $this->entityTypeManager
        ->getStorage('living_spaces_section')
        ->loadByProperties([
          'path' => $section,
          'id' => $group_sections,
        ]);

      if (!empty($section)) {
        $section = reset($section);
      }
    }

    // Check if we have subsection, if we do then we should definitely
    // have entity among section children with the path.
    if (is_object($section) && $sub_section) {
      $ids = array_column(
        $section->get(LIVING_SPACES_SECTIONS_FIELD)->getValue(),
        'target_id'
      );

      if ($ids) {
        $sub_section = $this->entityTypeManager
          ->getStorage('living_spaces_section')
          ->loadByProperties([
            'path' => $sub_section,
            'id' => $ids,
          ]);
        if (!empty($sub_section)) {
          $data = reset($sub_section);
        }
      }
    }
    elseif (is_object($section)) {
      $data = $section;
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionTypeEntity($section_type) {
    /** @var \Drupal\living_spaces_sections\Entity\LivingSpacesSectionTypeInterface $entity */
    $entity = $this->entityTypeManager
      ->getStorage('living_spaces_section_type')
      ->load($section_type);

    return $entity;
  }

}
