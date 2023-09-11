<?php

namespace Drupal\living_spaces_intranet;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of ban entities.
 */
class LivingSpacesBanListBuilder extends EntityListBuilder {

  /**
   * Returns the date.formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new LivingSpacesBanListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   Provides an interface for an entity type and its metadata.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   Defines the interface for entity storage classes.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Provides an interface defining a date formatter.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter) {
    parent::__construct($entity_type, $storage);

    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'id' => [
        'data' => $this->t('Ban ID'),
        'specifier' => 'id',
        'field' => 'id',
      ],
      'label' => [
        'data' => $this->t('Title'),
        'specifier' => 'label',
        'field' => 'label',
      ],
      'uid' => [
        'data' => $this->t('Author'),
      ],
      'changed' => [
        'data' => $this->t('Updated'),
        'specifier' => 'changed',
        'field' => 'changed',
      ],
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\living_spaces_intranet\Entity\LivingSpacesBan $entity */
    $row['id'] = $entity->id();
    $row['name']['data'] = $entity->toLink()->toRenderable();
    $row['uid'] = $entity->getOwner() ? $entity->getOwner()->getDisplayName() : t('Deleted employee');
    $row['changed'] = $this->dateFormatter->format($entity->getChangedTime(), 'short');

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('There are no ban yet.');

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery();
    $header = $this->buildHeader();
    $query->accessCheck();
    $query->tableSort($header);

    if ($this->limit) {
      $query->pager($this->limit);
    }

    return $query->execute();
  }

}
