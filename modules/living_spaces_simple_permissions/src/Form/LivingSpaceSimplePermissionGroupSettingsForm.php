<?php

namespace Drupal\living_spaces_simple_permissions\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\group\Access\GroupPermissionHandlerInterface;
use Drupal\user\PermissionHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for simple permission group settings.
 */
class LivingSpaceSimplePermissionGroupSettingsForm extends LivingSpaceSimplePermissionGlobalSettingsForm {

  /**
   * {@inheritdoc}
   */
  protected $configName = 'living_spaces_simple_permissions.group';

  /**
   * Returns the group.permissions service.
   *
   * @var \Drupal\group\Access\GroupPermissionHandlerInterface
   */
  protected $groupPermissions;

  /**
   * Constructs a new LivingSpaceSimplePermissionGroupSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\user\PermissionHandler $user_permissions
   *   Provides the available permissions based on yml files.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Interface for classes that manage a set of enabled modules.
   * @param \Drupal\group\Access\GroupPermissionHandlerInterface $group_permissions
   *   Defines an interface to list available permissions.
   */
  public function __construct(ConfigFactoryInterface $config_factory, PermissionHandler $user_permissions, ModuleHandlerInterface $module_handler, GroupPermissionHandlerInterface $group_permissions) {
    parent::__construct($config_factory, $user_permissions, $module_handler);

    $this->groupPermissions = $group_permissions;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('user.permissions'),
      $container->get('module_handler'),
      $container->get('group.permissions')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_simple_permission_group_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getModules() {
    $options = [];

    foreach ($this->groupPermissions->getPermissions() as $name => $info) {
      $options[$info['provider']] = $this->moduleHandler->getName($info['provider']);
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getPermissions($all = TRUE) {
    $options = [];

    foreach ($this->groupPermissions->getPermissions() as $name => $info) {
      $providerName = $this->moduleHandler->getName($info['provider']);
      $options[$providerName][$name] = $info['title'];
    }

    if (!$all) {
      foreach ($options as $module => $permissions) {
        if (1 == count($permissions)) {
          unset($options[$module]);
        }
      }
    }

    return $options;
  }

}
