<?php

namespace Drupal\living_spaces_event\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Form controller for the space event add and edit forms.
 */
class LivingSpaceEventForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $start = $form_state->getValue('field_start_date');
    $end = $form_state->getValue('field_end_date');

    if (isset($start[0]['value'], $end[0]['value']) &&
      $start[0]['value'] instanceof DrupalDateTime &&
      $end[0]['value'] instanceof DrupalDateTime &&
      $end[0]['value']->getTimestamp() < $start[0]['value']->getTimestamp()
    ) {
      $form_state->setErrorByName('field_start_date', $this->t('The end date must be later than the start date.'));
      $form_state->setErrorByName('field_end_date');
    }

    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEvent $entity */
    $entity = $this->entity;
    $args = [
      '@type' => $entity->getEventType()->label(),
      '%title' => $entity->label(),
    ];

    $this->messenger()->addStatus($this->operation == 'edit'
      ? $this->t('@type <b>%title</b> has been updated.', $args)
      : $this->t('@type <b>%title</b> has been created.', $args)
    );

    $form_state->setRedirect('entity.living_spaces_event.canonical', ['living_spaces_event' => $entity->id()]);
    return $status;
  }

}
