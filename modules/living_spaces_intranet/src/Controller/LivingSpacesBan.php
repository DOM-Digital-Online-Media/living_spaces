<?php

namespace Drupal\living_spaces_intranet\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\living_spaces_intranet\LivingSpacesBansManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * LivingSpacesBan class.
 */
class LivingSpacesBan extends ControllerBase {

  /**
   * Returns the living_spaces_bans.manager service.
   *
   * @var \Drupal\living_spaces_intranet\LivingSpacesBansManagerInterface
   */
  protected $banManager;

  /**
   * Returns the request_stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a LivingSpacesBan object.
   *
   * @param \Drupal\living_spaces_intranet\LivingSpacesBansManagerInterface $ban_manager
   *   Interface for ban manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack that controls the lifecycle of requests.
   */
  public function __construct(LivingSpacesBansManagerInterface $ban_manager, RequestStack $request_stack) {
    $this->banManager = $ban_manager;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('living_spaces_bans.manager'),
      $container->get('request_stack')
    );
  }

  /**
   * Callback for 'ban user' route.
   */
  public function ban(AccountInterface $user, $type, $length) {
    $data = [
      'bundle' => $type,
      'expire' => $length,
    ];
    $this->banManager->setUserBan($user, $data);

    $query = $this->requestStack->getCurrentRequest()->query;

    if ($query->has('destination')) {
      return new RedirectResponse($query->get('destination'));
    }

    $url = Url::fromRoute('<front>');
    return new RedirectResponse($url->toString());
  }

  /**
   * Access callback for 'ban user' route.
   */
  public function banAccess(AccountInterface $user, $type, $length) {
    $access = $this->currentUser()->hasPermission('administer ban') ||
      $this->currentUser()->hasPermission("create {$type} ban");

    return $access ? AccessResult::allowed() : AccessResult::forbidden();
  }

  /**
   * Callback for 'unban user' route.
   */
  public function unban(AccountInterface $user, $type) {
    $this->banManager->deleteUserBans($user, [$type]);

    $query = $this->requestStack->getCurrentRequest()->query;

    if ($query->has('destination')) {
      return new RedirectResponse($query->get('destination'));
    }

    $url = Url::fromRoute('<front>');
    return new RedirectResponse($url->toString());
  }

  /**
   * Access callback for 'unban user' route.
   */
  public function unbanAccess(AccountInterface $user, $type) {
    $access = $this->currentUser()->hasPermission('administer ban') ||
      $this->currentUser()->hasPermission("delete {$type} ban");

    return $access ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
