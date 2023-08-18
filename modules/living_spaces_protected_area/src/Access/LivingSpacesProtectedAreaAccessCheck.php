<?php

namespace Drupal\living_spaces_protected_area\Access;

use Drupal\Core\Routing\Access\AccessInterface as CoreAccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * LivingSpacesProtectedAreaAccessCheck class.
 */
class LivingSpacesProtectedAreaAccessCheck implements CoreAccessInterface {

  /**
   * Returns the request_stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Constructs a LivingSpacesProtectedAreaAccessCheck object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Request stack that controls the lifecycle of requests.
   */
  public function __construct(RequestStack $request) {
    $this->request = $request;
  }

  /**
   * Check access for '_access_protected_area' routes.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    $uuid = $this->request->getCurrentRequest()->query->get('uuid');
    $access = $account->hasPermission('access protected area');

    return !$uuid || $access || $account->isAnonymous() ? AccessResult::forbidden() : AccessResult::allowed();
  }

}
