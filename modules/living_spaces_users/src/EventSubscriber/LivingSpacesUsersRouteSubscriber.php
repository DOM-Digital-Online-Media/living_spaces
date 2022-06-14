<?php

namespace Drupal\living_spaces_users\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\UserInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Space users route subscriber.
 */
class LivingSpacesUsersRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.user.canonical')) {
      $route->setPath('/user/{user}/profile');
      $route->setDefault('_title_callback', __CLASS__ . '::userViewTitleCallback');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -300];
    return $events;
  }

  /**
   * Callback for user view page title.
   *
   * @param \Drupal\user\UserInterface $user
   *   User entity.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   User view page title.
   */
  public static function userViewTitleCallback(UserInterface $user) {
    return new TranslatableMarkup('Profile of @username', [
      '@username' => $user->getDisplayName(),
    ]);
  }

}
