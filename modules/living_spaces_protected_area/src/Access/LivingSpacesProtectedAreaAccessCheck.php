<?php

namespace Drupal\living_spaces_protected_area\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Http\RequestStack;

/**
 * LivingSpacesProtectedAreaAccessCheck class.
 */
class LivingSpacesProtectedAreaAccessCheck implements AccessInterface {

  /**
   * Returns the request_stack service.
   *
   * @var \Drupal\Core\Http\RequestStack
   */
  protected $request;

  /**
   * Constructs a LivingSpacesProtectedAreaAccessCheck object.
   *
   * @param \Drupal\Core\Http\RequestStack $request
   *   Forward-compatibility shim for Symfony's RequestStack.
   */
  public function __construct(RequestStack $request) {
    $this->request = $request;
  }

  /**
   * Check access for '_access_protected_area' routes.
   */
  public function access(RouteMatchInterface $route, AccountInterface $account) {
    $uuid = $this->request->getCurrentRequest()->query->get('uuid');
    $access = $account->hasPermission('access protected area');

    return !$uuid || $access || $account->isAnonymous() ? AccessResult::forbidden() : AccessResult::allowed();
  }

}
