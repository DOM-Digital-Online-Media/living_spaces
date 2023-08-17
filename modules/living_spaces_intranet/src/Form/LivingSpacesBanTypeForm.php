<?php

namespace Drupal\living_spaces_intranet\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for ban type add/edit forms.
 */
class LivingSpacesBanTypeForm extends BundleEntityFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\living_spaces_intranet\Entity\LivingSpacesBanTypeInterface $ban_type */
    $ban_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $ban_type->label(),
      '#description' => $this->t('Label for the ban type.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $ban_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\living_spaces_intranet\Entity\LivingSpacesBanType::load',
      ],
      '#disabled' => !$ban_type->isNew(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $ban_type->getDescription(),
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save ban type');

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $event_type = $this->entity;
    $status = $event_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label ban type.', [
          '%label' => $event_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label ban type.', [
          '%label' => $event_type->label(),
        ]));
    }

    $form_state->setRedirectUrl($event_type->toUrl('collection'));
    return $status;
  }

}
