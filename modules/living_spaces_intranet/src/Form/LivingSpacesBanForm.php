<?php

namespace Drupal\living_spaces_intranet\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the ban add and edit forms.
 */
class LivingSpacesBanForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $form_state->setRedirect('entity.living_spaces_ban.collection');
  }

}
