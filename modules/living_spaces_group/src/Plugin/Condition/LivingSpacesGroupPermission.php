<?php

namespace Drupal\living_spaces_group\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\group\Access\GroupPermissionCheckerInterface;
use Drupal\group\Access\GroupPermissionHandlerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Group Permission' condition.
 *
 * @Condition(
 *   id = "living_spaces_group_permission",
 *   label = @Translation("Group Permission"),
 *   context_definitions = {
 *     "group" = @ContextDefinition("entity:group", label = @Translation("Group")),
 *     "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *   }
 * )
 */
class LivingSpacesGroupPermission extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Group permission handler service.
   *
   * @var \Drupal\group\Access\GroupPermissionHandlerInterface
   */
  protected $permissionHandler;

  /**
   * Group permission checker service.
   *
   * @var \Drupal\group\Access\GroupPermissionCheckerInterface
   */
  protected $permissionChecker;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Creates a new LivingSpacesGroupPermission instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\group\Access\GroupPermissionHandlerInterface $permission_handler
   *   The group permission handler service.
   * @param \Drupal\group\Access\GroupPermissionCheckerInterface $permission_checker
   *   The group permission checker service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GroupPermissionHandlerInterface $permission_handler, GroupPermissionCheckerInterface $permission_checker, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->permissionHandler = $permission_handler;
    $this->permissionChecker = $permission_checker;
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
      $container->get('group.permissions'),
      $container->get('group_permission.checker'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'group_permission' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $permissions = [];
    foreach ($this->permissionHandler->getPermissions(TRUE) as $permission_name => $permission) {
      $display_name = $this->moduleHandler->getName($permission['provider']);
      $permissions[$display_name . ' : ' . $permission['section']][$permission_name] = strip_tags($permission['title']);
    }

    $form['group_permission'] = [
      '#title' => $this->t('Group permission'),
      '#empty_value' => '',
      '#type' => 'select',
      '#options' => $permissions,
      '#default_value' => $this->configuration['group_permission'],
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['group_permission'] = $form_state->getValue('group_permission');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $args = ['%permission' => $this->configuration['group_permission']];
    return $this->isNegated()
      ? $this->t('Does not have %permission group permission.', $args)
      : $this->t('Have %permission group permission.', $args);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $access = TRUE;

    $group = $this->getContextValue('group') instanceof GroupInterface
      ? $this->getContextValue('group') : NULL;
    $user = $this->getContextValue('user') instanceof UserInterface
      ? $this->getContextValue('user') : NULL;
    if (!empty($this->configuration['group_permission']) && $group && $user) {
      $access = $this->permissionChecker
        ->hasPermissionInGroup($this->configuration['group_permission'], $user, $group);
    }

    return $this->isNegated() ? !$access : $access;
  }

}
