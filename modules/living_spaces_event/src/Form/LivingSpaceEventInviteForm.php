<?php

namespace Drupal\living_spaces_event\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the space event invite add and edit forms.
 */
class LivingSpaceEventInviteForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);
    $form_state->setRedirect('entity.living_spaces_event.canonical', ['living_spaces_event' => $this->entity->get('event')->entity->id()]);

    return $status;
  }

}
