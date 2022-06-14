<?php

namespace Drupal\living_spaces_protected_area\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for access area type add/edit forms.
 */
class LivingSpacesAccessAreaTypeForm extends BundleEntityFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\living_spaces_protected_area\Entity\LivingSpacesProtectedAreaAccessAreaTypeInterface $access_area_type */
    $access_area_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $access_area_type->label(),
      '#description' => $this->t('Label for the access area type.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $access_area_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\living_spaces_protected_area\Entity\LivingSpacesProtectedAreaAccessAreaType::load',
      ],
      '#disabled' => !$access_area_type->isNew(),
    ];

    $form['description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textarea',
      '#default_value' => $access_area_type->getDescription(),
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save access area type');

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $access_area_type = $this->entity;
    $status = $access_area_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label access area type.', [
          '%label' => $access_area_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label access area type.', [
          '%label' => $access_area_type->label(),
        ]));
    }

    $form_state->setRedirectUrl($access_area_type->toUrl('collection'));
  }

}
