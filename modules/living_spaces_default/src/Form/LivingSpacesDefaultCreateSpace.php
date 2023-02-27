<?php

namespace Drupal\living_spaces_default\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\living_spaces_group\LivingSpacesGroupManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;

/**
 * Form handler for adding groups.
 */
class LivingSpacesDefaultCreateSpace extends FormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Returns the living_spaces_group.manager service.
   *
   * @var \Drupal\living_spaces_group\LivingSpacesGroupManagerInterface
   */
  protected $livingSpacesManager;

  /**
   * Returns the database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a LivingSpacesGroupForm form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Interface for classes that manage a set of enabled modules.
   * @param \Drupal\living_spaces_group\LivingSpacesGroupManagerInterface $living_spaces_manager
   *   Interface for group manager service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, LivingSpacesGroupManagerInterface $living_spaces_manager, Connection $database) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
    $this->livingSpacesManager = $living_spaces_manager;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('living_spaces_group.manager'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_default_create_space';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $group_types = $template_options = [];
    foreach ($this->entityTypeManager->getStorage('group_type')->loadMultiple() as $group_type) {
      if ($this->currentUser()->hasPermission("create {$group_type->id()} group")) {
        $group_types[] = $group_type->id();
      }
    }

    if ($group_types) {
      $query = $this->database->select('groups_field_data', 'gfd');
      $query->fields('gfd', ['id', 'label']);
      $query->condition('gfd.is_default', 1);
      $query->condition('gfd.type', $group_types, 'IN');
      $template_options = $query->execute()->fetchAllKeyed();
    }

    $form['template'] = [
      '#type' => 'select',
      '#title' => $this->t('Template'),
      '#options' => $template_options,
      '#empty_option' => $this->t(' - Select a value - '),
      '#empty_value' => '',
      '#default_value' => '',
      '#ajax' => [
        'callback' => '::templateAjaxCallback',
        'wrapper' => 'group-parent-wrapper'
      ],
    ];

    $info = $this->moduleHandler->invokeAll('living_spaces_group_type_info');
    $input = $form_state->getUserInput();

    $required = FALSE;
    $parent_options = $group_types = [];
    if (!empty($input['template'])) {
      $query = $this->database->select('groups_field_data', 'gfd');
      $query->fields('gfd', ['type']);
      $query->condition('gfd.id', $input['template']);

      if ($result = $query->execute()->fetchAssoc()) {
        if (isset($info[$result['type']]['parent_types'])) {
          $group_types = $info[$result['type']]['parent_types'];
          $required = TRUE;
        }
      }
    }

    if (empty($group_types) && $types = $this->livingSpacesManager->getLivingSpaceGroupTypes()) {
      $group_types = $types;
    }

    if ($group_types) {
      $query = $this->database->select('groups_field_data', 'gfd');
      $query->fields('gfd', ['id', 'label']);
      $query->condition('gfd.type', $group_types, 'IN');

      $group = $query->orConditionGroup();
      $group->isNull('gfd.is_default');
      $group->condition('gfd.is_default', 0);
      $query->condition($group);

      $parent_options = $query->execute()->fetchAllKeyed();
    }

    $form['parent'] = [
      '#type' => 'select',
      '#title' => $this->t('Parent'),
      '#options' => $parent_options,
      '#required' => $required,
      '#empty_option' => $this->t(' - Select a value - '),
      '#empty_value' => '',
      '#default_value' => '',
      '#prefix' => '<div id="group-parent-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * Provides ajax callback for template field.
   */
  public function templateAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['parent'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $storage = $this->entityTypeManager->getStorage('group');

    $query = !empty($values['parent']) ? ['parent[0][target_id]' => $values['parent']] : [];
    $group_type = 'living_space';

    if (!empty($values['template'])) {
      /** @var \Drupal\group\Entity\GroupInterface $group */
      $group = $storage->load($values['template']);

      $query['space'] = $group->uuid();
      $url = Url::fromRoute('entity.group.add_form', ['group_type' => $group->bundle()], ['query' => $query]);
    }
    else {
      $url = Url::fromRoute('entity.group.add_form', ['group_type' => $group_type], ['query' => $query]);
    }

    $form_state->setRedirectUrl($url);
  }

}
