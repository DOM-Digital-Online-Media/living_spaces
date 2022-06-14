<?php

namespace Drupal\living_spaces_event\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for event type add/edit forms.
 */
class LivingSpaceEventTypeForm extends BundleEntityFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventTypeInterface $event_type */
    $event_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $event_type->label(),
      '#description' => $this->t('Label for the space event type.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $event_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\living_spaces_event\Entity\LivingSpaceEventType::load',
      ],
      '#disabled' => !$event_type->isNew(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $event_type->getDescription(),
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save space event type');

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
        $this->messenger()->addMessage($this->t('Created the %label space event type.', [
          '%label' => $event_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label space event type.', [
          '%label' => $event_type->label(),
        ]));
    }

    $form_state->setRedirectUrl($event_type->toUrl('collection'));
  }

}
