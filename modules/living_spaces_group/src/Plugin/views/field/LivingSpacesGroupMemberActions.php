<?php

namespace Drupal\living_spaces_group\Plugin\views\field;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\example\ExampleInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides Space member actions field handler.
 *
 * @ViewsField("living_spaces_group_space_member_actions")
 */
class LivingSpacesGroupMemberActions extends FieldPluginBase {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Constructs a new SpaceMemberActions instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   The current route match service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, CurrentRouteMatch $current_route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentRouteMatch = $current_route_match;
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
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['group_field'] = ['default' => ''];
    $options['enabled_links'] = ['default' => []];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['group_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Group'),
      '#default_value' => $this->options['group_field'],
      '#required' => TRUE,
      '#options' => $this->getPreviousFieldLabels(),
    ];
    $form['enabled_links'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Links'),
      '#default_value' => $this->options['enabled_links'],
      '#required' => TRUE,
      '#options' => $this->moduleHandler
        ->invokeAll('living_spaces_group_actions_info'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\user\UserInterface $user */
    $user = $this->getEntity($values);

    /** @var \Drupal\views\Plugin\views\field\FieldHandlerInterface $group_field */
    $group_field = $this->displayHandler
      ->getHandler('field', $this->options['group_field']);
    /** @var \Drupal\group\Entity\GroupInterface $space */
    $space = $this->entityTypeManager->getStorage('group')
      ->load($group_field->getValue($values));

    /** @var \Drupal\file\FileInterface[] $picture */
    $picture = $user->get('user_picture')->referencedEntities();
    $img = [];
    if (!empty($picture)) {
      $img = [
        '#theme' => 'image_style',
        '#uri' => $picture[0]->getFileUri(),
        '#style_name' => 'thumbnail',
        '#attributes' => [
          'class' => ['rounded'],
        ],
      ];
    }

    $suffix = '';
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $values->_relationship_entities['gid'];
    $inherited_from_circle = $group->id() !== $this->currentRouteMatch->getRawParameter('group');
    if ($inherited_from_circle) {
      $suffix = ' (' . trim($group->label()) . ')';
    }

    $name = $user->getDisplayName() . $suffix;
    $links = $this->moduleHandler
      ->invokeAll(
        'living_spaces_group_actions_info',
        [FALSE, $space, $user, $inherited_from_circle]
      );
    $links = array_intersect_key($links, $this->options['enabled_links']);
    $output = [
      '#theme' => 'dropdown',
      '#id' => "space-actions-{$space->id()}-{$user->id()}",
      '#button_img' => $img,
      '#button_class' => 'btn-lg',
      '#button' => $name,
      '#links' => $links,
    ];
    $cache = CacheableMetadata::createFromObject($user);
    $cache->addCacheableDependency($space);
    $cache->applyTo($output);

    return $output;
  }

}
