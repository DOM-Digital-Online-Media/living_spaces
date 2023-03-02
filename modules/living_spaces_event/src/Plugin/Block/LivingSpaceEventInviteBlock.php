<?php

namespace Drupal\living_spaces_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Block with 'Invite users' form.
 *
 * @Block(
 *   id = "living_spaces_event_invite_block",
 *   admin_label = @Translation("Invite users"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "living_spaces_event" = @ContextDefinition("entity:living_spaces_event", required = TRUE, label = @Translation("Event"))
 *   }
 * )
 */
class LivingSpaceEventInviteBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the form_builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a LivingSpaceEventInviteBlock block.
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
    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInterface $event */
    $event = $this->getContextValue('living_spaces_event');

    if (!empty($event->in_preview)) {
      return [];
    }

    return $this->formBuilder->getForm('Drupal\living_spaces_event\Form\LivingSpaceEventInviteUsersForm', $event);
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
