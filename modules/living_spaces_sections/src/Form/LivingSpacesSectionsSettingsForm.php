<?php

namespace Drupal\living_spaces_sections\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\inline_entity_form\Element\InlineEntityForm;
use Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Form handler for enabling/configuring available sections and sub-sections.
 */
class LivingSpacesSectionsSettingsForm extends FormBase {

  /**
   * Living space group entity, exists when form initialised for a group.
   *
   * @var \Drupal\group\Entity\GroupInterface|null
   */
  protected $group;

  /**
   * Section entity, exists when form initialised for a section.
   *
   * @var \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface|null
   */
  protected $section;

  /**
   * Provides current_route_match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $route;

  /**
   * Provides entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Provides entity_type.bundle.info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Provides module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Living space sections manager.
   *
   * @var \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface
   */
  protected $sectionManager;

  /**
   * Current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->route = $container->get('current_route_match');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->entityTypeBundleInfo = $container->get('entity_type.bundle.info');
    $instance->moduleHandler = $container->get('module_handler');
    $instance->sectionManager = $container->get('living_spaces_sections.manager');
    $instance->currentPath = $container->get('path.current');

    // Set entity on which editing happens.
    switch ($instance->route->getRouteName()) {
      case 'living_spaces_sections.sections_form':
        $instance->group = $instance->route->getParameter('group');
        break;

      case 'living_spaces_sections.sub_sections_form':
        $group = $instance->route->getParameter('group');
        $section_path = $instance->route->getParameter('section');
        $instance->section = $instance->sectionManager->getSectionFromGroupByPath($group, $section_path);
        break;

    }

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_sections_settings_form';
  }

  /**
   * Returns page title for the form.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   Page title.
   */
  public function getTitle() {
    if ($this->group) {
      return $this->t('Section settings for @name', ['@name' => $this->group->label()]);
    }
    elseif ($this->section) {
      return $this->t('Sub-section settings for @name', ['@name' => $this->section->label()]);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;
    $form_storage = $form_state->getStorage();
    $section_types = $this->getAvailableSectionTypes();
    $enabled_sections = $this->getEnabledSectionTypes();
    $layout_builder_enabled = $this->moduleHandler->moduleExists('layout_builder');
    $entity_view_storage = $this->entityTypeManager->getStorage('entity_view_display');

    if (!empty($section_types)) {
      $class = !$this->currentUser()->hasPermission('administer living spaces sections settings') ? 'visually-hidden' : '';
      $form['sections'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Enable Sections'),
        '#options' => [],
        '#default_value' => [],
        '#disabled' => !empty($class),
        '#attributes' => [
          'class' => [$class],
        ],
      ];
      $options = &$form['sections']['#options'];
      $form_storage['group_sections'] = [];

      if ($enabled_sections) {
        $default_options = &$form['sections']['#default_value'];

        foreach ($enabled_sections as $section) {
          $form_storage['group_sections'][$section->bundle()] = $section;
          $default_options[$section->bundle()] = $section->bundle();
        }
      }

      foreach ($section_types as $machine_name => $type) {
        $options[$machine_name] = $type->label();
        $section = $form_storage['group_sections'][$machine_name] ?? NULL;
        $display = $layout_builder_enabled
          ? $entity_view_storage->load('living_spaces_section.' . $machine_name . '.default')
          : NULL;
        $custom_enabled = $display
          ? $display->getThirdPartySetting('layout_builder', 'allow_custom', FALSE)
          : FALSE;

        $form[$machine_name] = [
          '#type' => 'details',
          '#title' => $this->t('@plugin configuration', ['@plugin' => $type->label()]),
          '#open' => TRUE,
          '#states' => [
            'visible' => [
              ':input[name="sections[' . $machine_name . ']"]' => ['checked' => TRUE],
            ],
          ],
          'layout' => $custom_enabled && $section ? [
            '#type' => 'link',
            '#title' => $this->t('Edit layout for the section.'),
            '#url' => new Url('layout_builder.overrides.living_spaces_section.view', [
              'living_spaces_section' => $section->id(),
            ], [
              'query' => ['destination' => $this->currentPath->getPath()],
            ]),
          ] : NULL,
          'form' => [
            '#type' => 'inline_entity_form',
            '#entity_type' => 'living_spaces_section',
            '#bundle' => $machine_name,
            '#default_value' => $section,
            '#living_space' => $this->group ? $this->group : NULL,
            '#section' => $this->section ? $this->section : NULL,
            '#element_validate' => [[get_class(), 'inlineEntityFormValidate']]
          ],
        ];
      }
      $form_state->setStorage($form_storage);

      $form['submit'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Save settings'),
      ];
    }
    else {
      $form['empty'] = [
        '#type' => 'markup',
        '#markup' => $this->t('There are no available living space sections.'),
      ];
    }

    return $form;
  }

  /**
   * Validates inner inline entity form and check whether we need to save it.
   *
   * @param array $entity_form
   *   The entity form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function inlineEntityFormValidate(array &$entity_form, FormStateInterface $form_state) {
    $enabled = array_keys(array_filter($form_state->getValue('sections', [])));

    if (!in_array($entity_form['#bundle'], $enabled)) {
      $entity_form['#save_entity'] = FALSE;
      return;
    }

    InlineEntityForm::validateEntityForm($entity_form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_storage = $form_state->getStorage();
    $sections = array_keys(array_filter($form_state->getValue('sections')));
    $field_values = [];

    // Add new sections that were enabled for an entity.
    foreach ($sections as $section_type) {
      if (!array_key_exists($section_type, $form_storage['group_sections'])) {
        $form_storage['group_sections'][$section_type] = $form[$section_type]['form']['#entity'];
      }
      $field_values[]['target_id'] = $form_storage['group_sections'][$section_type]->id();
    }
    if ($entity = $this->setSections($field_values)) {
      $entity->save();
    }

    // Remove sections that were disabled for an entity.
    foreach ($form_storage['group_sections'] as $section_type => $section) {
      if (!in_array($section_type, $sections, TRUE)) {
        /** @var \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface $section */
        $section->delete();
      }
    }
  }

  /**
   * Returns available section types to be selected either for group or section.
   *
   * @return \Drupal\living_spaces_sections\Entity\LivingSpacesSectionTypeInterface[]
   *   Section type entity.
   */
  protected function getAvailableSectionTypes() {
    $result = [];
    if ($this->group) {
      $result = $this->entityTypeManager
        ->getStorage('living_spaces_section_type')
        ->loadByProperties(['parent' => '']);
    }
    elseif ($this->section) {
      $result = $this->entityTypeManager
        ->getStorage('living_spaces_section_type')
        ->loadByProperties(['parent' => $this->section->bundle()]);
    }

    return $result;
  }

  /**
   * Returns enabled section entities for group or section being edited.
   *
   * @return \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface[]
   *   Section entity.
   */
  protected function getEnabledSectionTypes() {
    if ($this->group) {
      return $this->sectionManager->getSectionsFromGroup($this->group);
    }
    elseif ($this->section) {
      return $this->sectionManager->getSubSectionsFromSection($this->section);
    }
    return [];
  }

  /**
   * Sets sections to the entity that is being edited and returns it.
   *
   * @param array $sections
   *   Sections value array designed to go to entity set() method.
   *
   * @return \Drupal\group\Entity\GroupInterface|\Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface|null
   *   Section entity.
   */
  protected function setSections(array $sections) {
    if ($this->group instanceof GroupInterface) {
      $entity_type = 'group';
      $entity_id = $this->group->id();
    }
    if ($this->section instanceof LivingSpacesSectionInterface) {
      $entity_type = 'living_spaces_section';
      $entity_id = $this->section->id();
    }
    // Get entity with latest changes and update sections field.
    if (isset($entity_type, $entity_id)) {
      $entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id);
      if ($entity->hasField(LIVING_SPACES_SECTIONS_FIELD)) {
        return $entity->set(LIVING_SPACES_SECTIONS_FIELD, $sections);
      }
    }
    return NULL;
  }

}
