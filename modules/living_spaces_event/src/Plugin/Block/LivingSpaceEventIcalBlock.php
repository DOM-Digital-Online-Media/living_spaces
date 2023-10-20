<?php

namespace Drupal\living_spaces_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Link;

/**
 * Block with 'LivingSpaceEventIcalBlock' block.
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
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->route = $route;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
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
    $access = $account->hasPermission('access ical event export');

    if (!$access && $gid = $this->route->getRawParameter('group')) {
      /** @var \Drupal\group\Entity\GroupInterface $group */
      $group = $this->entityTypeManager->getStorage('group')->load($gid);
      $access = $group->hasPermission('access ical event export', $account);
    }

    return $access ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
