<?php

namespace Drupal\living_spaces_event;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class LivingSpacesEventDatetime.
 */
class LivingSpacesEventDatetime {

  /**
   * Provides validate callback for datetime field.
   */
  public static function validateDatetime(&$element, FormStateInterface $form_state, &$complete_form) {
    $input = NestedArray::getValue($form_state->getValues(), $element['#parents']);

    if (isset($input['date'])) {
      $input['time'] = '23:59:59';
      $input['object'] = new DrupalDateTime($input['date'] . ' 23:59:59');

      $form_state->setValue($element['#name'], $input);
    }
  }

}
