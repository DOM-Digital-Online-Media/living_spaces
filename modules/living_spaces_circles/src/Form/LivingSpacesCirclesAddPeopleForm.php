<?php

namespace Drupal\living_spaces_circles\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Render\RendererInterface;

/**
 * Form handler for adding circles.
 */
class LivingSpacesCirclesAddPeopleForm extends FormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Returns the module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Returns the form_builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a LivingSpacesCirclesAddPeopleForm form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Defines an interface for turning a render array into a string.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Interface for classes that manage a set of enabled modules.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   Provides an interface for form building and processing.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, ModuleHandlerInterface $module_handler, FormBuilderInterface $formBuilder) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->moduleHandler = $module_handler;
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('module_handler'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_circles_add_people_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, GroupInterface $group = NULL) {
    if (!$group) {
      return [];
    }

    $labels = [];
    if (!$group->get('circles')->isEmpty()) {
      $labels = $this->getCircles($group);
    }

    $form['circles'] = [
      '#type' => 'item',
      '#title' => $this->t('Circles:'),
      '#markup' => $this->renderer->render($labels),
      '#access' => !empty($labels),
      '#weight' => 0,
    ];

    $url = Url::fromRoute('living_spaces_circles.circle_autocomplete', [], ['absolute' => TRUE])->getInternalPath();

    $form['circle'] = [
      '#type' => 'autocomplete_deluxe',
      '#autocomplete_deluxe_path' => $url,
      '#title' => $this->t('Circle'),
      '#multiple' => TRUE,
      '#target_type' => 'group',
      '#weight' => 2,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => '::ajaxSubmitForm',
        'event' => 'click',
        'progress' => [
          'type' => 'none',
        ],
      ],
      '#weight' => 3,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Provides the ajax submit callback.
   */
  public function ajaxSubmitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
    $response = new AjaxResponse();

    $errors = $message_list = [];
    if (empty($form_state->getValue('circle'))) {
      $errors[] = $this->t('The circle field is required.');
    }

    if (!$errors) {
      $info = $form_state->getBuildInfo();
      $values = $form_state->getValues();

      /** @var \Drupal\group\Entity\GroupInterface $group */
      $group = $info['args'][0];

      $input = [];
      foreach ($values['circle'] as $value) {
        $input[$value['target_id']] = $value['target_id'];
      }

      foreach ($group->get('circles')->getValue() as $value) {
        if (in_array($value['target_id'], $input)) {
          unset($input[$value['target_id']]);
        }
      }

      $message_list = ['status' => [$this->t('The group has been added.')]];
      if ($input) {
        foreach ($input as $item) {
          $group->get('circles')->appendItem($item);
        }
        $group->save();

        $message = $this->formatPlural(count($input), 'The group has been added.', 'The groups have been added.');
        $message_list = ['status' => [$message]];
      }

      $labels = $this->getCircles($group);
      $form['circles']['#markup'] = $this->renderer->render($labels);
      $form['circles']['#access'] = TRUE;

      $form['message'] = [
        '#theme' => 'status_messages',
        '#message_list' => $message_list,
        '#weight' => 1,
      ];

      $form['circle']['#value'] = '';
      $form['circle']['value_field']['#value'] = '';

      $response->addCommand(new ReplaceCommand('*[id^=living-spaces-circles-add-people-form]', $form));

      // Update Members Tab.
      $group_ids = [$group->id()];
      if (!$group->get('circles')->isEmpty()) {
        $group_ids = array_merge($group_ids, array_column($group->get('circles')->getValue(), 'target_id'));
      }

      $view = Views::getView('members');
      $member_tab = [
        '#type' => 'view',
        '#view' => $view,
        '#name' => 'members',
        '#display_id' => 'members_by_role',
        '#arguments' => [implode('+', $group_ids)],
      ];
      $response->addCommand(new ReplaceCommand('#members-tabsContent .views-element-container', $member_tab));

    }
    else {
      foreach ($errors as $error) {
        $message_list['error'][] = $error;
      }

      $form['message'] = [
        '#theme' => 'status_messages',
        '#message_list' => $message_list,
        '#weight' => 1,
      ];

      $response->addCommand(new ReplaceCommand('*[id^=living-spaces-circles-add-people-form]', $form));
    }

    return $response;
  }

  /**
   * Helper to get all circle groups.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group that has the circle groups.
   *
   * @return array
   *   An array of related circles with dropdown actions.
   */
  public function getCircles(GroupInterface $group) {
    $labels = [];

    foreach ($group->get('circles')->getValue() as $value) {
      $circle = $this->entityTypeManager->getStorage('group')->load($value['target_id']);
      $hook = 'living_spaces_circles_actions_info';
      $labels[] = [
        '#theme' => 'dropdown',
        '#id' => "circle-actions-{$group->id()}-{$circle->id()}",
        '#button_class' => 'btn-sm',
        '#button' => $circle->label(),
        '#links' => $this->moduleHandler->invokeAll($hook, [$group, $circle]),
      ];
    }

    return $labels;
  }

}
