<?php

namespace Drupal\living_spaces_simple_permissions\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\user\PermissionHandler;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Form handler for simple permission global settings.
 */
class LivingSpaceSimplePermissionGlobalSettingsForm extends ConfigFormBase {

  /**
   * Returns the name of the config.
   *
   * @var string
   */
  protected $configName = 'living_spaces_simple_permissions.global';

  /**
   * Returns the user.permissions service.
   *
   * @var \Drupal\user\PermissionHandler
   */
  protected $userPermissions;

  /**
   * Returns the module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new LivingSpaceSimplePermissionGlobalSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\user\PermissionHandler $user_permissions
   *   Provides the available permissions based on yml files.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Interface for classes that manage a set of enabled modules.
   */
  public function __construct(ConfigFactoryInterface $config_factory, PermissionHandler $user_permissions, ModuleHandlerInterface $module_handler) {
    parent::__construct($config_factory);

    $this->userPermissions = $user_permissions;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('user.permissions'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_simple_permission_global_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      $this->configName,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable($this->configName);

    $form['modules'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Modules'),
      '#options' => $this->getModules(),
      '#default_value' => !empty($config->get('modules')) ? $config->get('modules') : [],
    ];

    $form['permissions'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Hide permissions'),
      '#tree' => TRUE,
      '#prefix' => '<div id="permissions-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    $count = $form_state->get('permissions_count');
    if (empty($count)) {
      $config_count = !empty($config->get('permissions')) ? count($config->get('permissions')) : 1;

      $form_state->set('permissions_count', $config_count);
      $count = $config_count;
    }

    $permissions = $this->getPermissions(FALSE);
    for ($i = 0; $i < $count; $i++) {
      $form['permissions'][$i]['name'] = [
        '#type' => 'select',
        '#title' => $this->t('Permission name'),
        '#options' => $permissions,
        '#empty_option' => $this->t(' - Select a value - '),
        '#empty_value' => 0,
        '#default_value' => !empty($config->get('permissions')[$i]) ? $config->get('permissions')[$i] : '',
      ];
    }

    $form['permissions']['actions'] = [
      '#type' => 'actions',
    ];

    $form['permissions']['actions']['permission'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add new permission'),
      '#submit' => ['::addMore'],
      '#ajax' => [
        'callback' => '::addMoreCallback',
        'wrapper' => 'permissions-fieldset-wrapper',
      ],
    ];

    $form['names'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Rename permissions'),
      '#tree' => TRUE,
      '#prefix' => '<div id="names-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    $count = $form_state->get('names_count');
    if (empty($count)) {
      $config_count = !empty($config->get('names')) ? count($config->get('names')) : 1;

      $form_state->set('names_count', $config_count);
      $count = $config_count;
    }

    $permissions = $this->getPermissions();
    for ($i = 0; $i < $count; $i++) {
      $form['names'][$i]['old'] = [
        '#type' => 'select',
        '#title' => $this->t('Permission name'),
        '#options' => $permissions,
        '#empty_option' => $this->t(' - Select a value - '),
        '#empty_value' => 0,
        '#default_value' => !empty($config->get('names')[$i]['old']) ? $config->get('names')[$i]['old'] : '',
      ];

      $form['names'][$i]['new'] = [
        '#type' => 'textfield',
        '#title' => $this->t('New permission name'),
        '#default_value' => !empty($config->get('names')[$i]['new']) ? $config->get('names')[$i]['new'] : '',
      ];

      $form['names'][$i]['desc'] = [
        '#type' => 'textfield',
        '#title' => $this->t('New permission description'),
        '#default_value' => !empty($config->get('names')[$i]['desc']) ? $config->get('names')[$i]['desc'] : '',
      ];
    }

    $form['names']['actions'] = [
      '#type' => 'actions',
    ];

    $form['names']['actions']['name'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#submit' => ['::addMore'],
      '#ajax' => [
        'callback' => '::addMoreCallback',
        'wrapper' => 'names-fieldset-wrapper',
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Helper to get options for module field.
   *
   * @return array
   *   An array of all modules.
   */
  public function getModules() {
    $options = [];

    foreach ($this->userPermissions->getPermissions() as $name => $info) {
      $options[$info['provider']] = $this->moduleHandler->getName($info['provider']);
    }

    return $options;
  }

  /**
   * Ajax handler for the 'Add more' button.
   */
  public function addMoreCallback(array &$form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();

    switch ($element['#ajax']['wrapper']) {
      case 'permissions-fieldset-wrapper':
        return $form['permissions'];

      case 'names-fieldset-wrapper':
        return $form['names'];
    }
  }

  /**
   * Submit handler for the 'Add more' button.
   */
  public function addMore(array &$form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();

    switch ($element['#ajax']['wrapper']) {
      case 'permissions-fieldset-wrapper':
        $form_state->set('permissions_count', $form_state->get('permissions_count') + 1);
        break;

      case 'names-fieldset-wrapper':
        $form_state->set('names_count', $form_state->get('names_count') + 1);
        break;
    }

    $form_state->setRebuild();
  }

  /**
   * Helper to get options for permissions field.
   *
   * @param bool $all
   *   An indicator for returning all permissions.
   *
   * @return array
   *   An array of all permissions.
   */
  public function getPermissions($all = TRUE) {
    $options = [];

    foreach ($this->userPermissions->getPermissions() as $name => $info) {
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

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($modules = $form_state->getValue('modules')) {
      foreach ($modules as $key => $value) {
        if (empty($modules[$key])) {
          unset($modules[$key]);
        }
      }
    }

    if ($permissions = $form_state->getValue('permissions')) {
      foreach ($permissions as $key => $value) {
        if (empty($value['name'])) {
          unset($permissions[$key]);
        }
        else {
          $permissions[$key] = $permissions[$key]['name'];
        }
      }
    }

    if ($names = $form_state->getValue('names')) {
      foreach ($names as $key => $value) {
        if (empty($value['old']) && empty($value['new']) && empty($value['desc'])) {
          unset($names[$key]);
        }
      }
    }

    $this->configFactory->getEditable($this->configName)
      ->set('modules', $modules)
      ->set('permissions', $permissions)
      ->set('names', $names)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
