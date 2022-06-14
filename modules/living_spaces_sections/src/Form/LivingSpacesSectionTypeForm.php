<?php

namespace Drupal\living_spaces_sections\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for section type add/edit forms.
 */
class LivingSpacesSectionTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\living_spaces_sections\Entity\LivingSpacesSectionTypeInterface $section_type */
    $section_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $section_type->label(),
      '#description' => $this->t('Label for the living space section type.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $section_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\living_spaces_sections\Entity\LivingSpacesSectionType::load',
      ],
      '#disabled' => !$section_type->isNew(),
    ];

    if ($section_types = $this->getAvailableParentSectionTypes($section_type->id())) {
      $form['parent'] = [
        '#type' => 'select',
        '#title' => $this->t('Parent section type'),
        '#default_value' => $section_type->getParent(),
        '#options' => $section_types,
        '#description' => $this->t('This option enables a section type to be displayed inside another section on a group view.'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $section_type = $this->entity;
    $status = $section_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label living space section type.', [
          '%label' => $section_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label living space section type.', [
          '%label' => $section_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($section_type->toUrl('collection'));
  }

  /**
   * Returns available section types to be selected as parent type.
   *
   * @param string|null $current
   *   Section type id that is currently being edited.
   *
   * @return array
   *   Associative array where keys are ids and values are labels.
   */
  public function getAvailableParentSectionTypes($current = NULL) {
    $return = [
      NULL => $this->t('- None -'),
    ];

    $storage = $this->entityTypeManager
      ->getStorage($this->entity->getEntityTypeId());
    $section_types = $storage->loadByProperties(['parent' => '']);
    foreach ($section_types as $section_type) {
      $return[$section_type->id()] = $section_type->label();
    }

    if ($current) {
      unset($return[$current]);
    }

    return $return;
  }

}
