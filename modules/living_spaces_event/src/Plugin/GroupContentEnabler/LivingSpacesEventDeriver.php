<?php

namespace Drupal\living_spaces_event\Plugin\GroupContentEnabler;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * LivingSpacesEventDeriver class.
 */
class LivingSpacesEventDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new LivingSpacesEventDeriver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    foreach ($this->entityTypeManager->getStorage('living_spaces_event_type')->loadMultiple() as $name => $event_type) {
      $label = $event_type->label();

      $this->derivatives[$name] = [
        'entity_bundle' => $name,
        'label' => t('Space event (@type)', ['@type' => $label]),
        'description' => t('Adds %type space event to groups both publicly and privately.', ['%type' => $label]),
      ] + $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
