<?php

namespace Drupal\living_spaces_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Block with 'LivingSpacesEventInvitedUsersBlock' block.
 *
 * @Block(
 *   id = "living_spaces_event_invited_users_block",
 *   admin_label = @Translation("Invited users"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "living_spaces_event" = @ContextDefinition("entity:living_spaces_event", required = TRUE, label = @Translation("Event"))
 *   }
 * )
 */
class LivingSpacesEventInvitedUsersBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the current_user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a LivingSpacesEventInvitedUsersBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Defines an account interface which represents the current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInterface $event */
    $event = $this->getContextValue('living_spaces_event');

    if (!empty($event->in_preview)) {
      return $build;
    }

    $event_invite_manager = $this->entityTypeManager->getStorage('living_spaces_event_invite');

    $query = $event_invite_manager->getQuery();
    $query->condition('event', $event->id());

    $rows = [];
    if ($ids = $query->execute()) {
      /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInviteInterface $invite */
      foreach ($event_invite_manager->loadMultiple($ids) as $invite) {
        if (!$invite->get('status')->isEmpty()) {
          /** @var \Drupal\taxonomy\TermInterface $status */
          $status = $invite->get('status')->entity;

          $owner = $invite->getOwner();
          $rows[$status->uuid()][] = $owner->getDisplayName();
        }
      }
    }

    $build['list'] = [
      '#theme' => 'living_spaces_event_invited_list',
      '#rows' => $rows,
      '#cache' => [
        'tags' => Cache::mergeTags($event->getCacheTags(), []),
      ],
    ];

    return $build;
  }

}
