<?php

namespace Drupal\living_spaces_group\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\living_spaces_group\LivingSpacesGroupManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a 'Is living space' condition.
 *
 * @Condition(
 *   id = "living_spaces_group_is_living_group",
 *   label = @Translation("Is living space"),
 *   context_definitions = {
 *     "group" = @ContextDefinition("entity:group", label = @Translation("Group"))
 *   }
 * )
 */
class LivingSpacesGroupIsLivingSpace extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the living_spaces_group.manager service.
   *
   * @var \Drupal\living_spaces_group\LivingSpacesGroupManagerInterface
   */
  protected $livingSpacesManager;

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a new LivingSpacesGroupIsLivingSpace instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\living_spaces_group\LivingSpacesGroupManagerInterface $living_spaces_manager
   *   Interface for group manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LivingSpacesGroupManagerInterface $living_spaces_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->livingSpacesManager = $living_spaces_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('living_spaces_group.manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if ($this->isNegated()) {
      return $this->t('The group is not living space.');
    }

    return $this->t('The group is living space.');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $group = $this->getContextValue('group');

    if (!$group instanceof GroupInterface) {
      $group = $this->entityTypeManager->getStorage('group')->load($group);
    }

    if ($this->isNegated()) {
      return !$this->livingSpacesManager->isLivingSpace($group->bundle());
    }

    return $this->livingSpacesManager->isLivingSpace($group->bundle());
  }

}
