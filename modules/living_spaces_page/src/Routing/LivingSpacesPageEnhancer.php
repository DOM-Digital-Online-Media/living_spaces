<?php

namespace Drupal\living_spaces_page\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\EnhancerInterface;
use Drupal\Core\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * LivingSpacesPageEnhancer class.
 */
class LivingSpacesPageEnhancer implements EnhancerInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a LivingSpacesPageEnhancer object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Returns whether the enhancer runs on the current route.
   */
  protected function applies(Route $route) {
    $parameters = $route->getOption('parameters') ?: [];
    return !empty($parameters['node']);
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

    /** @var \Drupal\node\Entity\Node $node */
    $node = $defaults['node'];
    if ('page' == $node->bundle()) {
      $entity_manager = $this->entityTypeManager->getStorage('group');
      $query = $entity_manager->getQuery();
      $query->condition('content_sections', $node->id());
      $query->accessCheck(FALSE);

      if ($group_ids = $query->execute()) {
        $gid = reset($group_ids);

        $route->setDefault('group', $gid);
        $defaults['group'] = $gid;
      }
    }

    return $defaults;
  }

}
