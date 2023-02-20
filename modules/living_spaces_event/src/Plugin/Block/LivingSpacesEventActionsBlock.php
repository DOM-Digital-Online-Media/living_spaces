<?php

namespace Drupal\living_spaces_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Block with 'LivingSpacesEventActionsBlock' block.
 *
 * @Block(
 *   id = "living_spaces_event_actions_block",
 *   admin_label = @Translation("Event actions"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "group" = @ContextDefinition("entity:group", required = TRUE, label = @Translation("Group"))
 *   }
 * )
 */
class LivingSpacesEventActionsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a LivingSpacesEventActionsBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Interface for classes that manage a set of enabled modules.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $space = $this->getContextValue('group');

    if (!empty($space->in_preview)) {
      return [];
    }

    return [
      '#theme' => 'dropdown',
      '#id' => "event-actions-{$space->id()}",
      '#button_class' => 'btn-primary',
      '#button' => $this->t('- Select action -'),
      '#links' => $this->moduleHandler->invokeAll('living_spaces_event_action_info', [$space]),
    ];
  }

}
