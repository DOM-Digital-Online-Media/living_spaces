<?php

namespace Drupal\living_spaces_protected_area;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Provides a listing of access area type entities.
 */
class LivingSpacesAccessAreaTypeBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Access area type');
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
    /** @var \Drupal\living_spaces_protected_area\Entity\LivingSpacesProtectedAreaAccessAreaTypeInterface $entity*/
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
    $build['table']['#empty'] = $this->t('No access area types available. <a href="@link">Add access area type</a>.', [
      '@link' => Url::fromRoute('entity.living_spaces_access_area_type.add_form')->toString(),
    ]);

    return $build;
  }

}
