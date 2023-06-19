<?php

namespace Drupal\living_spaces_circles\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Url;

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
   * Constructs a LivingSpacesCirclesRemoveCircleForm form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
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
      '#value' => $this->t('Are you sure you want to delete the circle item <b>@circle</b>?', ['@circle' => $circle->label()]),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    $form['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#attributes' => ['class' => ['button']],
      '#url' => Url::fromRoute('entity.group.canonical', ['group' => $group->id()]),
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
    $form_state->setRedirect('entity.group.canonical', ['group' => $group->id()]);
  }

}
