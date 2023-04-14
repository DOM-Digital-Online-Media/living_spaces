<?php

namespace Drupal\living_spaces_group\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A handler to provide display for membership.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("living_spaces_group_current_user_member")
 */
class LivingSpacesGroupCurrentUserMember extends FieldPluginBase {

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    $user = $this->entityTypeManager->getStorage('user')
      ->load($this->currentUser->id());
    $spaces = array_column($user->get('joined_spaces')->getValue(), 'target_id');
    return in_array($values->_entity->id(), $spaces);
  }

}
