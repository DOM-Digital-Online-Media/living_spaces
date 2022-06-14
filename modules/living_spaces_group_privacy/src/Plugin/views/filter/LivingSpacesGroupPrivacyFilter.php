<?php

namespace Drupal\living_spaces_group_privacy\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyManager;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Filter handler to group privacy.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("living_spaces_group_privacy_filter")
 */
class LivingSpacesGroupPrivacyFilter extends InOperator implements ContainerFactoryPluginInterface {

  /**
   * Space privacy manager service.
   *
   * @var \Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyManager
   */
  protected $privacyManager;

  /**
   * Constructs a LivingSpacesGroupPrivacyFilter object.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, LivingSpacesGroupPrivacyManager $privacy_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->privacyManager = $privacy_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.living_spaces_group_privacy')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = $this->t('Group privacy');
    $this->definition['options callback'] = [$this, 'generateOptions'];
  }

  /**
   * Helper function that generates the options.
   */
  public function generateOptions() {
    $options = [];

    foreach ($this->privacyManager->getPrivacyPlugins() as $instance) {
      $options[$instance->id()] = $instance->label();
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    if (!empty($this->value)) {
      parent::validate();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    if (!empty($this->value)) {
      parent::query();
    }
  }

}
