<?php

namespace Drupal\living_spaces_protected_area\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the access area add and edit forms.
 */
class LivingSpacesAccessAreaForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    /** @var \Drupal\living_spaces_protected_area\Entity\LivingSpacesProtectedAreaAccessArea $entity */
    $entity = $this->entity;
    $args = [
      '@type' => $entity->getAccessAreaType()->label(),
      '%title' => $entity->label(),
    ];

    $this->messenger()->addStatus($this->operation == 'edit'
      ? $this->t('@type <b>%title</b> has been updated.', $args)
      : $this->t('@type <b>%title</b> has been created.', $args)
    );

    $form_state->setRedirect('view.access_area.access_area');
  }

}
