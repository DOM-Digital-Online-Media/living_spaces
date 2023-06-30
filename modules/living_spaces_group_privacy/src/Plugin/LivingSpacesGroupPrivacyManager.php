<?php

namespace Drupal\living_spaces_group_privacy\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\group\Entity\GroupInterface;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a living_spaces_group_privacy plugin manager.
 */
class LivingSpacesGroupPrivacyManager extends DefaultPluginManager implements LivingSpacesGroupPrivacyManagerInterface {

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a LivingSpacesGroupPrivacyManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, LoggerInterface $logger, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct(
      'Plugin/LivingSpacesGroupPrivacy',
      $namespaces,
      $module_handler,
      'Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyInterface',
      'Drupal\living_spaces_group_privacy\Annotation\LivingSpacesGroupPrivacy'
    );

    $this->alterInfo('living_spaces_group_privacy');
    $this->setCacheBackend($cache_backend, 'living_spaces_group_privacy_plugins');
    $this->logger = $logger;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getPrivacyPlugins(GroupInterface $group = NULL) {
    $instances = [];

    foreach ($this->getDefinitions() as $name => $definition) {
      $instances[$name] = $this->createInstance($name);
    }

    if ($group) {
      $type = $group->get('living_space_privacy')->getString();
      return $type && !empty($instances[$type]) ? $instances[$type] : NULL;
    }

    return $instances;
  }

  /**
   * {@inheritdoc}
   */
  public function getRelatedGroups(NodeInterface $node) {
    $entity_manager = $this->entityTypeManager;
    /** @var \Drupal\group\Entity\Storage\GroupRelationshipStorageInterface $relationship_storage */
    $relationship_storage = $entity_manager->getStorage('group_relationship');
    $group_manager = $entity_manager->getStorage('group');

    $groups = [];
    foreach ($relationship_storage->loadByEntity($node) as $relationship) {
      $groups[] = $relationship->getGroup();
    }

    if ($this->moduleHandler->moduleExists('living_spaces_page')) {
      $query = $group_manager->getQuery();
      $query->condition('content_sections', $node->id());
      $query->accessCheck(FALSE);

      if ($group_ids = $query->execute()) {
        foreach ($group_manager->loadMultiple($group_ids) as $group) {
          $groups[] = $group;
        }
      }
    }

    return $groups;
  }

}
