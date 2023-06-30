<?php

namespace Drupal\living_spaces_group_privacy;

use Drupal\entity\QueryAccess\QueryAccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * LivingSpacesGroupPrivacyEventSubscriber class.
 */
class LivingSpacesGroupPrivacyEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['entity.query_access.group'] = 'onGroupQueryAccess';
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function onGroupQueryAccess(QueryAccessEvent $event) {
    $conditions = $event->getConditions();
    $account = $event->getAccount();
    $operation = $event->getOperation();
    // Add conditions based on account's access to "{$operation} {$privacy} group" permission.
  }

}
