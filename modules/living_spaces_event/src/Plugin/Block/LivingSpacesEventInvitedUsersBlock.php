<?php

namespace Drupal\living_spaces_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RedirectDestinationInterface;
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
   * Returns the redirect.destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirect;

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

    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInterface $event */
    $event = $this->getContextValue('living_spaces_event');

    if (!empty($event->in_preview)) {
      return $build;
    }

    $tags = $event->getCacheTags();

    $taxonomy_manager = $this->entityTypeManager->getStorage('taxonomy_term');

    $query = $taxonomy_manager->getQuery();
    $query->condition('vid', 'event_status');

    $statuses = [];
    if ($tids = $query->execute()) {
      /** @var \Drupal\taxonomy\TermInterface $term */
      foreach ($taxonomy_manager->loadMultiple($tids) as $term) {
        $statuses[$term->uuid()] = $term->id();
      }
    }

    $event_invite_manager = $this->entityTypeManager->getStorage('living_spaces_event_invite');

    $query = $event_invite_manager->getQuery();
    $query->condition('event', $event->id());

    $rows = [];
    if ($ids = $query->execute()) {
      /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInviteInterface $invite */
      foreach ($event_invite_manager->loadMultiple($ids) as $invite) {
        $tags = Cache::mergeTags($tags, $invite->getCacheTags());

        if (!$invite->get('status')->isEmpty()) {
          /** @var \Drupal\user\UserInterface $owner */
          $owner = $invite->getOwner();

          /** @var \Drupal\taxonomy\TermInterface $status */
          $status = $invite->get('status')->entity;

          $suffix = '';
          if ($this->currentUser->id() == $owner->id() ||
            $this->currentUser->hasPermission('administer living spaces event invite')
          ) {
            $accept = $decline = FALSE;

            switch ($status->uuid()) {
              case LIVING_SPACES_EVENT_OWN_STATUS:
              case LIVING_SPACES_EVENT_INVITED_STATUS:
                $accept = $decline = TRUE;
                break;

              case LIVING_SPACES_EVENT_DECLINED_STATUS:
                $accept = TRUE;
                break;

              case LIVING_SPACES_EVENT_ACCEPTED_STATUS:
                $decline = TRUE;
                break;

            }

            if ($accept) {
              $options = [
                'attributes' => [
                  'class' => ['btn', 'btn-secondary', 'btn-primary', 'delete'],
                ],
                'query' => [
                  'destination' => $this->redirect->get(),
                ],
              ];

              $suffix .= Link::createFromRoute($this->t('Delete'), 'living_spaces_event.event_status', [
                'living_spaces_event_invite' => $invite->id(),
                'status' => 'delete',
              ], $options)->toString();

              $options = [
                'attributes' => [
                  'class' => ['btn', 'btn-primary', 'accept'],
                ],
                'query' => [
                  'destination' => $this->redirect->get(),
                ],
              ];

              $suffix .= Link::createFromRoute($this->t('Accept'), 'living_spaces_event.event_status', [
                'living_spaces_event_invite' => $invite->id(),
                'status' => $statuses[LIVING_SPACES_EVENT_ACCEPTED_STATUS] ? $statuses[LIVING_SPACES_EVENT_ACCEPTED_STATUS] : '',
              ], $options)->toString();
            }

            if ($decline) {
              $options = [
                'attributes' => [
                  'class' => ['btn', 'btn-primary', 'decline'],
                ],
                'query' => [
                  'destination' => $this->redirect->get(),
                ],
              ];

              $suffix .= Link::createFromRoute($this->t('Decline'), 'living_spaces_event.event_status', [
                'living_spaces_event_invite' => $invite->id(),
                'status' => $statuses[LIVING_SPACES_EVENT_DECLINED_STATUS] ? $statuses[LIVING_SPACES_EVENT_DECLINED_STATUS] : '',
              ], $options)->toString();
            }
          }

          $rows[$status->uuid()][] = [
            '#type' => 'markup',
            '#markup' => $owner->toLink($owner->getDisplayName())->toString(),
            '#suffix' => $suffix,
          ];
        }
      }
    }

    $build['list'] = [
      '#theme' => 'living_spaces_event_invited_list',
      '#rows' => $rows,
      '#prefix' => $rows ? '<div class="title">' . $this->t('Invitation') . '</div>' : '',
      '#cache' => [
        'tags' => $tags,
      ],
    ];

    return $build;
  }

}
