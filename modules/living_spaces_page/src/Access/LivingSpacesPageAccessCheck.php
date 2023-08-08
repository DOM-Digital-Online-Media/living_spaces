<?php

namespace Drupal\living_spaces_page\Access;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface as CoreAccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * LivingSpacesPageAccessCheck class.
 */
class LivingSpacesPageAccessCheck implements CoreAccessInterface {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the request_stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Constructs a LivingSpacesProtectedAreaAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Request stack that controls the lifecycle of requests.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request) {
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request;
  }

  /**
   * Check access for node.add route.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    if ('node.add' == $route_match->getRouteName() &&
      'page' == $route_match->getRawParameter('node_type') &&
      $space = $this->request->getCurrentRequest()->query->get('space')
    ) {
      /** @var \Drupal\group\Entity\Group $group */
      if ($group = $this->entityTypeManager->getStorage('group')->load($space)) {
        if ($group->hasPermission('create page entities', $account)) {
          return AccessResult::allowed();
        }
      }
    }

    return AccessResult::neutral();
  }

}
