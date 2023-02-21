<?php

namespace Drupal\living_spaces_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;

/**
 * Block with 'LivingSpacesEventStatusBlock' block.
 *
 * @Block(
 *   id = "living_spaces_event_status_block",
 *   admin_label = @Translation("Change event status"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "living_spaces_event" = @ContextDefinition("entity:living_spaces_event", required = TRUE, label = @Translation("Event"))
 *   }
 * )
 */
class LivingSpacesEventStatusBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * Returns the redirect.destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirect;

  /**
   * Constructs a LivingSpacesEventStatusBlock block.
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
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect
   *   Provides an interface for redirect destinations.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user, RedirectDestinationInterface $redirect) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->redirect = $redirect;
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
      $container->get('current_user'),
      $container->get('redirect.destination')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $event = $this->getContextValue('living_spaces_event');

    if (!empty($event->in_preview)) {
      return $build;
    }

    if ($invite_ids = living_spaces_event_check_user_status($event->id(), $this->currentUser->id())) {
      $invite_id = reset($invite_ids);

      /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInviteInterface $invite */
      $invite = $this->entityTypeManager->getStorage('living_spaces_event_invite')->load($invite_id);

      $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
        'uuid' => LIVING_SPACES_EVENT_ACCEPTED_STATUS,
      ]);

      if ($terms && $invite->get('status')->getString() != reset($terms)->id()) {
        $build['accept'] = [
          '#type' => 'link',
          '#title' => $this->t('Accept'),
          '#attributes' => ['class' => ['btn', 'btn-primary']],
          '#url' => Url::fromRoute('living_spaces_event.event_status', [
            'living_spaces_event_invite' => $invite->id(),
            'status' => $terms ? reset($terms)->id() : '',
          ], [
            'query' => ['destination' => $this->redirect->get()],
          ]),
        ];
      }
    }

    return $build;
  }

}
