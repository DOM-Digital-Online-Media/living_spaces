<?php

namespace Drupal\living_spaces_group\EventSubscriber;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\default_content\Event\DefaultContentEvents;
use Drupal\default_content\Event\ImportEvent;

/**
 * Class LivingSpacesGroupEventSubScriber.
 */
class LivingSpacesGroupEventSubScriber implements EventSubscriberInterface {

  /**
   * Returns the module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new LivingSpacesGroupEventSubScriber object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handle
   *   Interface for classes that manage a set of enabled modules.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[DefaultContentEvents::IMPORT][] = array('import');

    return $events;
  }

  /**
   * React to default content import event.
   *
   * @param \Drupal\default_content\Event\ImportEvent $event
   *   Import Event
   */
  public function import(ImportEvent $event) {
    $entities = $event->getImportedEntities();
    foreach ($entities as $entity) {
      if ($entity instanceof GroupInterface) {
        $uuids = $this->moduleHandler->invokeAll('living_spaces_group_default_spaces');
        if ($uuids && in_array($entity->uuid(), $uuids)) {
          $memberships = $entity->getRelationships('group_membership');

          foreach ($memberships as $membership) {
            $membership->delete();
          }
        }
      }
    }
  }

}
