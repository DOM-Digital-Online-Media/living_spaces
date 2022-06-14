<?php

namespace Drupal\living_spaces_group\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $formBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
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

    return $access ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
