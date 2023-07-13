<?php

namespace Drupal\living_spaces_group;

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
    // @todo: load group content from route.
    if ($entity = $this->doGetEntity($route_match, $_entity)) {
      return $this->t('Delete %label', ['%label' => $entity->label()]);
    }
  }

}
