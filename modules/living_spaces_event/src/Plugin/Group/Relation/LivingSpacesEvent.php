<?php

namespace Drupal\living_spaces_event\Plugin\Group\Relation;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Plugin\Group\Relation\GroupRelationBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a content enabler for space events.
 *
 * @GroupRelationType(
 *   id = "living_spaces_event",
 *   label = @Translation("Space event"),
 *   description = @Translation("Adds space events to groups both publicly and privately."),
 *   entity_type_id = "living_spaces_event",
 *   entity_access = TRUE,
 *   reference_label = @Translation("Title"),
 *   reference_description = @Translation("The title of the space event to add to the group"),
 *   deriver = "Drupal\living_spaces_event\Plugin\Group\Relation\LivingSpacesEventDeriver",
 * )
 */
class LivingSpacesEvent extends GroupRelationBase implements ContainerFactoryPluginInterface {

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
   * Constructs a new LivingSpacesEvent object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Defines an account interface which represents the current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
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
   * Retrieves the space event type this plugin supports.
   *
   * @return \Drupal\living_spaces_event\Entity\LivingSpaceEventTypeInterface
   *   The space event type this plugin supports.
   */
  protected function getSpaceEventType() {
    return $this->entityTypeManager
      ->getStorage('living_spaces_event_type')
      ->load($this->getRelationType()->getEntityBundle());
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config['entity_cardinality'] = 1;
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $info = $this->t("This field has been disabled by the plugin to guarantee the functionality that's expected of it.");
    $form['entity_cardinality']['#disabled'] = TRUE;
    $form['entity_cardinality']['#description'] .= '<br /><em>' . $info . '</em>';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    $dependencies['config'][] = 'living_spaces_event.living_spaces_event_type.' . $this->getRelationType()->getEntityBundle();

    return $dependencies;
  }

}
