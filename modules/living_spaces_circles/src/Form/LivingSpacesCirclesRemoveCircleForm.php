<?php

namespace Drupal\living_spaces_circles\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Url;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Form handler for removing circle.
 */
class LivingSpacesCirclesRemoveCircleForm extends FormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProvider
   */
  protected $routeProvider;

  /**
   * Constructs a LivingSpacesCirclesRemoveCircleForm form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Routing\RouteProvider $route_provider
   *   The Route Provider.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RouteProvider $route_provider) {
    $this->entityTypeManager = $entity_type_manager;
    $this->routeProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('router.route_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_circles_remove_circle_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, GroupInterface $group = NULL, GroupInterface $circle = NULL) {
    if (!$group || !$circle) {
      return [];
    }

    $form['message'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t(
        'Are you sure you want to remove the circle item <b>@circle</b> from the group <b>@group</b>?',
        ['@circle' => $circle->label(), '@group' => $group->label()]
      ),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    $members_route_name = 'page_manager.page_view_living_space_members_living_space_members-layout_builder-1';
    try {
      $this->routeProvider->getRouteByName($members_route_name);
      $url = Url::fromRoute($members_route_name, ['group' => $group->id()]);
    }
    catch (RouteNotFoundException $exception) {
      $url = Url::fromRoute('entity.group.canonical', ['group' => $group->id()]);
    }

    $form['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#attributes' => ['class' => ['button']],
      '#url' => $url,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $info = $form_state->getBuildInfo();

    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $info['args'][0];
    /** @var \Drupal\group\Entity\GroupInterface $circle */
    $circle = $info['args'][1];

    foreach ($group->get('circles')->getValue() as $index => $value) {
      if ($circle->id() == $value['target_id']) {
        $group->get('circles')->removeItem($index);
        break;
      }
    }

    $group->save();

    $members_route_name = 'page_manager.page_view_living_space_members_living_space_members-layout_builder-1';
    try {
      $this->routeProvider->getRouteByName($members_route_name);
      $form_state->setRedirect($members_route_name, ['group' => $group->id()]);
    }
    catch (RouteNotFoundException $exception) {
      $form_state->setRedirect('entity.group.canonical', ['group' => $group->id()]);
    }
  }

}
