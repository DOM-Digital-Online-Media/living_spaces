<?php

namespace Drupal\living_spaces_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
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
   * Returns the renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Defines an interface for turning a render array into a string.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user, RedirectDestinationInterface $redirect, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->redirect = $redirect;
    $this->renderer = $renderer;
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
      $container->get('redirect.destination'),
      $container->get('renderer')
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

    if ($invite_ids = living_spaces_event_check_user_status($event->id(), $this->currentUser->id())) {
      $invite_id = reset($invite_ids);

      $title = $this->t('Would you like to accept the invitation for this event?');

      /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInviteInterface $invite */
      $invite = $this->entityTypeManager->getStorage('living_spaces_event_invite')->load($invite_id);

      $accept = $decline = FALSE;
      /** @var \Drupal\taxonomy\TermInterface $status */
      if ($status = $invite->get('status')->entity) {

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
      }

      if ($accept) {
        $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
          'uuid' => LIVING_SPACES_EVENT_ACCEPTED_STATUS,
        ]);

        $build['accept'] = [
          '#type' => 'link',
          '#title' => $this->t('Accept'),
          '#attributes' => ['class' => [
            'btn',
            'btn-primary',
            'accept',
          ]],
          '#url' => Url::fromRoute('living_spaces_event.event_status', [
            'living_spaces_event_invite' => $invite->id(),
            'status' => $terms ? reset($terms)->id() : '',
          ], [
            'query' => [
              'destination' => $this->redirect->get(),
            ],
          ]),
          '#cache' => [
            'tags' => Cache::mergeTags($event->getCacheTags(), $invite->getCacheTags()),
          ],
        ];
      }

      if ($decline) {
        $title = $this->t('You have accepted the invitation to this event.');

        $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
          'uuid' => LIVING_SPACES_EVENT_DECLINED_STATUS,
        ]);

        $build['decline'] = [
          '#type' => 'link',
          '#title' => $this->t('Decline'),
          '#attributes' => ['class' => [
            'btn',
            'btn-primary',
            'decline',
          ]],
          '#url' => Url::fromRoute('living_spaces_event.event_status', [
            'living_spaces_event_invite' => $invite->id(),
            'status' => $terms ? reset($terms)->id() : '',
          ], [
            'query' => [
              'destination' => $this->redirect->get(),
            ],
          ]),
          '#cache' => [
            'tags' => Cache::mergeTags($event->getCacheTags(), $invite->getCacheTags()),
          ],
        ];
      }
    }

    if (!empty($build)) {
      $head = [
        '#type' => 'markup',
        '#markup' => "<div class='title'>{$title}</div>",
      ];

      $build['#prefix'] = $this->renderer->render($head);
    }

    return $build;
  }

}
