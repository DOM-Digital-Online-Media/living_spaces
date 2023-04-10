<?php

namespace Drupal\living_spaces_group\Plugin\views\filter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\filter\InOperator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Filter handler to joined spaces.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("living_spaces_group_joined_spaces")
 */
class LivingSpacesGroupJoinedSpaces extends InOperator {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the current_user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a LivingSpacesUsersJoinedSpaces object.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    $joined_spaces_ids = array_keys($this->generateOptions());
    if (!empty($options['value'])) {
      $options['value'] = $joined_spaces_ids;
    }
    if (!empty($options['group_info']['group_items'][1]['value'])) {
      $options['group_info']['group_items'][1]['value'] = $joined_spaces_ids;
    }
    if (!empty($options['group_info']['group_items'][2]['value'])) {
      $options['group_info']['group_items'][2]['value'] = $joined_spaces_ids;
    }
    parent::init($view, $display, $options);
    $this->valueTitle = $this->t('Joined spaces');
    $this->definition['options callback'] = [$this, 'generateOptions'];
  }

  /**
   * Helper function that generates the options.
   */
  public function generateOptions() {
    $options = [];

    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    foreach ($user->get('joined_spaces')->getValue() as $value) {
      if ($space = $this->entityTypeManager->getStorage('group')->load($value['target_id'])) {
        $options[$value['target_id']] = $space->label();
      }
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
