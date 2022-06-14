<?php

namespace Drupal\living_spaces_subgroup\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Url;

/**
 * Form handler for removing child.
 */
class LivingSpacesSubgroupRemoveChildForm extends FormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a LivingSpacesSubgroupRemoveChildForm form.
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
    return 'living_spaces_subgroup_remove_child_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, GroupInterface $child = NULL) {
    if (!$child || $child->get('parent')->isEmpty()) {
      return [];
    }

    $form['message'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t('Are you sure you want to delete the child item <b>@child</b>?', ['@child' => $child->label()]),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    $form['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#attributes' => ['class' => ['button']],
      '#url' => Url::fromRoute('entity.group.canonical', ['group' => $child->get('parent')->entity->id()]),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $info = $form_state->getBuildInfo();

    $child = $info['args'][0];
    $parent = $child->get('parent')->entity->id();

    $child->set('parent', NULL);
    $child->save();

    $form_state->setRedirect('entity.group.canonical', ['group' => $parent]);
  }

}
