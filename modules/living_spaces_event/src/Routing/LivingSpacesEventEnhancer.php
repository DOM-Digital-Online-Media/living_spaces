<?php

namespace Drupal\living_spaces_event\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\EnhancerInterface;
use Drupal\Core\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * LivingSpacesEventEnhancer class.
 */
class LivingSpacesEventEnhancer implements EnhancerInterface {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a LivingSpacesEventEnhancer object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Provides an interface for entity type managers.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Returns whether the enhancer runs on the current route.
   */
  protected function applies(Route $route) {
    $parameters = $route->getOption('parameters') ?: [];

    return !empty($parameters['living_spaces_event']) || !empty($parameters['node']);
  }

  /**
   * {@inheritdoc}
   */
  public function enhance(array $defaults, Request $request) {
    /** @var \Symfony\Component\Routing\Route $route */
    $route = $defaults[RouteObjectInterface::ROUTE_OBJECT];
    if (!$this->applies($route)) {
      return $defaults;
    }

    $gid = NULL;
    if (!empty($defaults['living_spaces_event'])) {
      /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInterface $event */
      $event = $defaults['living_spaces_event'];

      if ($group = $event->get('space')->entity) {
        $gid = $group->id();
      }
    }

    if (!empty($defaults['node'])) {
      /** @var \Drupal\node\NodeInterface $node */
      $node = $defaults['node'];

      if (in_array($node->bundle(), ['agenda', 'protocol'])) {
        if ($group = $node->get('space')->entity) {
          $gid = $group->id();
        }
      }
    }

    if ($gid) {
      $route->setDefault('group', $gid);
      $defaults['group'] = $gid;
    }

    return $defaults;
  }

}
