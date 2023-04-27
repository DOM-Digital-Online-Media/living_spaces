<?php

namespace Drupal\living_spaces_group\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\user\PermissionHandler;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Form handler for exclude spaces settings.
 */
class LivingSpaceExcludeSpacesForm extends ConfigFormBase {

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
   * Constructs a new LivingSpaceExcludeSpacesForm object.
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
    return 'living_spaces_exclude_spaces';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['living_spaces_exclude_spaces'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('living_spaces_exclude_spaces');

    $form['spaces'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Spaces'),
      '#options' => [],
      '#default_value' => !empty($config->get('spaces')) ? $config->get('spaces') : [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('living_spaces_exclude_spaces')
      ->set('spaces', $form_state->getValue('spaces'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
