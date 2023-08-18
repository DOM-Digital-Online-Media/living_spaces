<?php

namespace Drupal\living_spaces_protected_area\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the space access grant add and edit forms.
 */
class LivingSpacesAccessGrantForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);
    $form_state->setRedirect('view.access_grant.access_grant');
    return $status;
  }

}
