<?php

namespace Drupal\living_spaces_group_privacy\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a 'Group privacy access' condition.
 *
 * @Condition(
 *   id = "living_spaces_group_privacy_access",
 *   label = @Translation("Group privacy access"),
 *   context_definitions = {
 *     "group" = @ContextDefinition("entity:group", label = @Translation("Group")),
 *     "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *   }
 * )
 */
class LivingSpacesGroupPrivacyAccess extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a new LivingSpacesGroupPrivacyAccess instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['operation' => 'view'] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $types = [
      'view' => $this->t('View'),
      'update' => $this->t('Update'),
      'delete' => $this->t('Delete'),
      'join' => $this->t('Join'),
    ];

    $form['operation'] = [
      '#title' => $this->t('Type'),
      '#type' => 'select',
      '#options' => $types,
      '#default_value' => $this->configuration['operation'],
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['operation'] = $form_state->getValue('operation');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $operation = $this->configuration['operation'];
    return $this->t('@op the space by its privacy type.', ['@op' => ucfirst($operation)]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $group = $this->getContextValue('group');

    if (!$group instanceof GroupInterface) {
      $group = $this->entityTypeManager->getStorage('group')->load($group);
    }

    $type = $group->get('living_space_privacy')->getString();
    $operation = $this->configuration['operation'];
    if ($this->isNegated()) {
      return !$group->hasPermission("{$operation} {$type} group", $this->getContextValue('user'));
    }

    return $group->hasPermission("{$operation} {$type} group", $this->getContextValue('user'));
  }

}
