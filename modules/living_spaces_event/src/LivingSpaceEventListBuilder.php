<?php

namespace Drupal\living_spaces_event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines a class to build a listing of Space Event entities.
 */
class LivingSpaceEventListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'id' => [
        'data' => $this->t('Space event ID'),
        'specifier' => 'id',
        'field' => 'id',
      ],
      'label' => [
        'data' => $this->t('Title'),
        'specifier' => 'label',
        'field' => 'label',
      ],
      'type' => [
        'data' => $this->t('Space event type'),
        'specifier' => 'type',
        'field' => 'type',
      ],
      'status' => [
        'data' => $this->t('Status'),
        'specifier' => 'status',
        'field' => 'status',
      ],
      'uid' => [
        'data' => $this->t('Author'),
      ],
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEvent $entity */
    $row['id'] = $entity->id();
    $row['name']['data'] = $entity->toLink()->toRenderable();
    $row['bundle'] = $entity->getEventType()->label();
    $row['status'] = $entity->isPublished() ? $this->t('Published') : $this->t('Unpublished');
    $row['uid'] = $entity->getOwner() ? $entity->getOwner()->getDisplayName() : t('Deleted employee');

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('There are no space events yet.');

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
