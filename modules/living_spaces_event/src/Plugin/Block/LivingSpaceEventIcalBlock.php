<?php

namespace Drupal\living_spaces_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Link;

/**
 * Block with 'Invite users' form.
 *
 * @Block(
 *   id = "living_spaces_event_ical_block",
 *   admin_label = @Translation("iCal"),
 *   category = @Translation("Living Spaces")
 * )
 */
class LivingSpaceEventIcalBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the current_route_match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $route;

  /**
   * Constructs a LivingSpaceEventIcalBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   Provides an interface for classes representing the result of routing.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->route = $route;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($gid = $this->route->getRawParameter('group')) {
      return [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => Link::createFromRoute($this->t('Export calendar file'), 'living_spaces_event.ical', [
          'group' => $gid,
        ], [
          'attributes' => [
            'class' => ['ical-icon', 'btn', 'btn-default'],
          ],
        ])->toString(),
        '#prefix' => '<h2>' . $this->t('Integrate appointment(s) into your own calendar') . '</h2>',
      ];
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInterface $event */
    $event = $this->getContextValue('living_spaces_event');

    return $event->access('update') ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
