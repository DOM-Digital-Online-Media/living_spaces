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
    /** @var \Symfony\Component\HttpFoundation\ParameterBag $parameters */
    $parameters = $route_match->getRawParameters();

    /** @var \Drupal\group\Entity\GroupContentInterface $content */
    if ($parameters->get('group_content', NULL) &&
      $content = $this->entityTypeManager->getStorage('group_content')->load($parameters->get('group_content'))
    ) {
      return $this->t('Delete %label', ['%label' => $content->getEntity()->label()]);
    }

    return parent::deleteTitle($route_match, $_entity);
  }

}
