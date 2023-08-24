<?php

namespace Drupal\living_spaces_group;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\group\Entity\Controller\GroupTypeListBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of group type entities.
 */
class LivingSpacesGroupTypeListBuilder extends GroupTypeListBuilder {

  /**
   * Returns the config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Constructs a LivingSpacesGroupTypeListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   Provides an interface for an entity type and its metadata.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   Defines the interface for entity storage classes.
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Defines the configuration object factory.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, ConfigFactory $config) {
    parent::__construct($entity_type, $storage);

    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityListQuery(): QueryInterface {
    $query = parent::getEntityListQuery();

    if ($spaces = $this->config->get('living_spaces_group.exclude_spaces')->get('spaces')) {
      $query->condition('id', $spaces, 'NOT IN');
    }

    return $query;
  }

}
