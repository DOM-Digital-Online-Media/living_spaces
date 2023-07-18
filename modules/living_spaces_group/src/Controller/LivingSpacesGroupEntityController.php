<?php

namespace Drupal\living_spaces_group\Controller;

use Drupal\Core\Entity\Controller\EntityController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * LivingSpacesGroupEntityController class.
 */
class LivingSpacesGroupEntityController extends EntityController {

  /**
   * {@inheritdoc}
   */
  public function deleteTitle(RouteMatchInterface $route_match, EntityInterface $_entity = NULL) {
    $parameters = $route_match->getRawParameters();

    /** @var \Drupal\group\Entity\GroupRelationshipInterface $relationship */
    if ($parameters->get('group_relationship', NULL) &&
      $relationship = $this->entityTypeManager->getStorage('group_relationship')->load($parameters->get('group_relationship'))
    ) {
      return $this->t('Delete %label', ['%label' => $relationship->getEntity()->label()]);
    }

    return parent::deleteTitle($route_match, $_entity);
  }

}
