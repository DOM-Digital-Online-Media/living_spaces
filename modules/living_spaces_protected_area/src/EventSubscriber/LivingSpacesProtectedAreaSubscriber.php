<?php

namespace Drupal\living_spaces_protected_area\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\living_spaces_protected_area\Entity\LivingSpacesProtectedAreaAccessArea;
use Drupal\Core\Url;

/**
 * LivingSpacesProtectedAreaSubscriber class.
 */
class LivingSpacesProtectedAreaSubscriber implements EventSubscriberInterface {

  /**
   * Returns the current_user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new LivingSpacesProtectedAreaSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Defines an account interface which represents the current user.
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[KernelEvents::REQUEST][] = ['checkForRedirection'];

    return $events;
  }

  /**
   * This method is called whenever the KernelEvents::REQUEST event is dispatched.
   */
  public function checkForRedirection(GetResponseEvent $event) {
    if ($this->currentUser->hasPermission('access protected area')) {
      return;
    }

    $access = LivingSpacesProtectedAreaAccessArea::getForUser($this->currentUser);
    if (is_array($access) && !$access['status']) {
      $entity = $access['entity'];
      $options = ['query' => ['uuid' => "{$entity->getEntityTypeId()}:{$entity->uuid()}"]];
      $url = Url::fromRoute('living_spaces_protected_area.protected_area', [], $options)->toString();

      $event->setResponse(new RedirectResponse($url));
    }
  }

}
