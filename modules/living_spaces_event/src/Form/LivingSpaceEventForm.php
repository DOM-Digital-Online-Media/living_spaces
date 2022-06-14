<?php

namespace Drupal\living_spaces_event\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the space event add and edit forms.
 */
class LivingSpaceEventForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

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

    $form_state->setRedirect('entity.living_spaces_event.canonical', ['living_spaces_event' => $this->entity->id()]);
  }

}
