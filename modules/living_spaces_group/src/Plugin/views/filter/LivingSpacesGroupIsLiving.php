<?php

namespace Drupal\living_spaces_group\Plugin\views\filter;

use Drupal\living_spaces_group\LivingSpacesGroupManagerInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter handler to group living space.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("living_spaces_group_is_living")
 */
class LivingSpacesGroupIsLiving extends InOperator implements ContainerFactoryPluginInterface {

  /**
   * Returns the living_spaces_group.manager service.
   *
   * @var \Drupal\living_spaces_group\LivingSpacesGroupManagerInterface
   */
  protected $livingSpacesManager;

  /**
   * Constructs a LivingGroupPrivacy object.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, LivingSpacesGroupManagerInterface $living_spaces_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->livingSpacesManager = $living_spaces_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('living_spaces_group.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    $options['exposed'] = FALSE;
    $options['admin_label'] = $this->t('Living space');

    parent::init($view, $display, $options);
    $this->definition['options callback'] = [$this, 'generateOptions'];
  }

  /**
   * Helper function that generates the options.
   */
  public function generateOptions() {
    return $this->livingSpacesManager->getLivingSpaceGroupTypes();
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    $this->value = $this->livingSpacesManager->getLivingSpaceGroupTypes();
    parent::validate();
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->value = $this->livingSpacesManager->getLivingSpaceGroupTypes();
    parent::query();
  }

}
