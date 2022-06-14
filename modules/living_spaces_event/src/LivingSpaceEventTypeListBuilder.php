<?php

namespace Drupal\living_spaces_event;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Provides a listing of spaces event type entities.
 */
class LivingSpaceEventTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Space event type');
    $header['id'] = $this->t('Machine name');
    $header['description'] = [
      'data' => $this->t('Description'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventTypeInterface $entity*/
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['description']['data'] = ['#markup' => $entity->getDescription()];

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('No space event types available. <a href="@link">Add space event type</a>.', [
      '@link' => Url::fromRoute('entity.living_spaces_event_type.add_form')->toString(),
    ]);

    return $build;
  }

}
