<?php

namespace Drupal\living_spaces_group_privacy\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyManager;
use Drupal\views\ResultRow;

/**
 * Field handler to group privacy.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("living_spaces_group_privacy_field")
 */
class LivingSpacesGroupPrivacyField extends FieldPluginBase {

  /**
   * Space privacy manager service.
   *
   * @var \Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyManager
   */
  protected $privacyManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('plugin.manager.living_spaces_group_privacy'));
  }

  /**
   * Constructs a LivingSpacesGroupPrivacyField object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LivingSpacesGroupPrivacyManager $privacy_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->privacyManager = $privacy_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $result = '';

    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $values->_entity;
    if ($instance = $this->privacyManager->getPrivacyPlugins($group)) {
      $result = $instance->label();
    }

    return ['#markup' => $result];
  }

}
