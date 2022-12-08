<?php

namespace Drupal\living_spaces_group\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Block with 'Group membership' form.
 *
 * @Block(
 *   id = "living_spaces_group_membership_block",
 *   admin_label = @Translation("Membership form"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "group" = @ContextDefinition("entity:group", required = TRUE, label = @Translation("Space"))
 *   }
 * )
 */
class LivingSpacesGroupMembershipBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the form_builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a LivingSpacesGroupMembershipBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   Provides an interface for form building and processing.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   The current route match service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    FormBuilderInterface $formBuilder,
    EntityTypeManagerInterface $entity_type_manager,
    CurrentRouteMatch $current_route_match
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentRouteMatch = $current_route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('entity_type.manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $this->getContextValue('group');

    if ($group->getGroupType()->hasContentPlugin('group_membership')) {
      return $this->formBuilder->getForm('Drupal\living_spaces_group\Form\LivingSpacesGroupMembershipForm', $group);
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $this->getContextValue('group');
    $access = $account->hasPermission('administer members') || $group->hasPermission('administer members', $account);

    if ($account->hasPermission('add members to administered space')) {
      $group = $this->currentRouteMatch->getParameter('group');
      if (!$group instanceof GroupInterface) {
        $group = $this->entityTypeManager->getStorage('group')->load($group);
      }
      if ($group) {
        $role_storage = $this->entityTypeManager->getStorage('group_role');
        $roles = $role_storage->loadByUserAndGroup($account, $group);
        foreach ($roles as $role) {
          if ($role->get('is_space_admin')) {
            return AccessResult::allowed();
          }
        }
      }
    }

    return $access ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
