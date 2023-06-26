<?php

namespace Drupal\living_spaces_group\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides LivingSpacesGroupChangeRoleAction action.
 *
 * @Action(
 *   id = "living_spaces_group_change_role_action",
 *   label = @Translation("Change user role"),
 *   type = "user",
 *   confirm_form_route_name = "living_spaces_group.multiple_change_role_confirm"
 * )
 */
class LivingSpacesGroupChangeRoleAction extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the tempstore.private service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Returns the current_user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Returns the current_route_match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $route;

  /**
   * Constructs a LivingSpacesGroupChangeRoleAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   Creates a PrivateTempStore object for a given collection.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Defines an account interface which represents the current user.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   Provides an interface for classes representing the result of routing.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $temp_store_factory, AccountInterface $current_user, RouteMatchInterface $route) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentUser = $current_user;
    $this->tempStoreFactory = $temp_store_factory;
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
      $container->get('tempstore.private'),
      $container->get('current_user'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $gid = $this->route->getRawParameter('group');
    $this->tempStoreFactory->get('living_spaces_group_change_role')->set($this->currentUser->id(), [
      'users' => $entities,
      'group' => $gid,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    $this->executeMultiple([$object]);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $user = $account ?: $this->currentUser;
    return $user->hasPermission('manage living spaces');
  }

}
