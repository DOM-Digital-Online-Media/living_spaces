<?php

namespace Drupal\living_spaces_group\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides LivingSpacesGroupRemoveUserAction action.
 *
 * @Action(
 *   id = "living_spaces_group_remove_user_action",
 *   label = @Translation("Remove user from the group"),
 *   type = "user"
 * )
 */
class LivingSpacesGroupRemoveUserAction extends ActionBase implements ContainerFactoryPluginInterface {

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
   * Returns the current_user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a LivingSpacesGroupRemoveUserAction object.
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
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Defines an account interface which represents the current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->route = $route;
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
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $gid = $this->route->getRawParameter('group');
    if ($gid && $group = $this->entityTypeManager->getStorage('group')->load($gid)) {
      /** @var \Drupal\user\Entity\User $user */
      foreach ($entities as $user) {
        $content = $group->getContent('group_membership', ['entity_id' => $user->id()]);
        /** @var \Drupal\group\Entity\GroupContent $item */
        foreach ($content as $item) {
          $item->delete();
        }
      }
    }
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
    $user = $account ? $account : $this->currentUser;
    return $user->hasPermission('manage living spaces');
  }

}
