<?php

namespace Drupal\living_spaces_event;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Plugin\Group\RelationHandler\OperationProviderInterface;
use Drupal\group\Plugin\Group\RelationHandler\OperationProviderTrait;

class LivingSpacesEventOperationProvider implements OperationProviderInterface {
  use OperationProviderTrait;

  /**
   * Constructs a new LivingSpacesEventOperationProvider.
   *
   * @param \Drupal\group\Plugin\Group\RelationHandler\OperationProviderInterface $parent
   *   The default operation provider.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(OperationProviderInterface $parent, EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user, TranslationInterface $string_translation) {
    $this->parent = $parent;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->stringTranslation = $string_translation;
  }

  public function getGroupOperations(GroupInterface $group) {
    $operations = $this->parent->getGroupOperations($group);

    $event_types = $this->entityTypeManager
      ->getStorage('living_spaces_event_type')
      ->loadMultiple();
    foreach ($event_types as $name => $event_type) {
      $plugin_id = "living_spaces_event:$name";
      if ($group->hasPermission("create {$plugin_id} entity", $this->currentUser)) {
        $route_params = ['group' => $group->id(), 'plugin_id' => $plugin_id];
        $operations["living_spaces_event-create-$name"] = [
          'title' => $this->t('Add @type', ['@type' => $event_type->label()]),
          'url' => new Url('entity.group_relationship.create_form', $route_params),
          'weight' => 30,
        ];
      }
    }

    return $operations;
  }

}
