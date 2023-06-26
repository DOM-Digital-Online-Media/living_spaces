<?php

namespace Drupal\living_spaces_users\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a notifications block.
 *
 * @Block(
 *   id = "living_spaces_users_notifications",
 *   admin_label = @Translation("User notifications"),
 *   category = @Translation("Living Spaces"),
 * )
 */
class LivingSpacesUsersNotificationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Returns the current_user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a LivingSpacesUsersNotificationBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Interface for classes that manage a set of enabled modules.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Defines an account interface which represents the current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->moduleHandler = $module_handler;
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
      $container->get('module_handler'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#theme' => 'navs',
      '#attached' => [
        'library' => [
          'living_spaces_barrio/inner_view',
        ],
      ],
    ];

    $build['#items'] = $this->moduleHandler->invokeAll('living_spaces_users_notification_menu_item_info');
    $this->moduleHandler->alter('living_spaces_users_notification_menu_item_info', $build['#items']);

    foreach ($build['#items'] as &$link) {
      $link['weight'] = !empty($link['weight']) ? $link['weight'] : 0;
    }
    array_multisort(array_column($build['#items'], 'weight'), SORT_NUMERIC, $build['#items']);

    return $build;
  }

}
