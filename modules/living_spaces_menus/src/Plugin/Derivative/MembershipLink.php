<?php

namespace Drupal\living_spaces_menus\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derivative class that provides the menu links for the Membership.
 */
class MembershipLink extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Creates a MembershipLink instance.
   */
  public function __construct($base_plugin_id, AccountInterface $current_user) {
    $this->currentUser = $current_user;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];
    if ($view = Views::getView('membership')) {
      $view->setDisplay('menu_member_of_spaces');
      $view->execute();
      $view_result = $view->result;

      if (!empty($view_result)) {
        foreach ($view->result as $row) {
          /** @var \Drupal\group\Entity\GroupInterface $group */
          if ($group = $row->_entity) {
            $links[$group->id()] = [
              'title' => $group->label(),
              'parent' => 'menu_link_content:436e7a5e-7eb0-4ba7-928c-b9064865f174',
              '#cache' => [
                'tags' => ['user:' . $this->currentUser->id()],
              ],
              'route_name' => $group->toUrl()->getRouteName(),
              'route_parameters' => ['group' => $group->id()],
            ] + $base_plugin_definition;
          }
        }
      }
    }

    return $links;
  }

}
