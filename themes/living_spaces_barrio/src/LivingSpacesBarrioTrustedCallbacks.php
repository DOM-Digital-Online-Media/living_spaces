<?php

namespace Drupal\living_spaces_barrio;

use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Implements trusted callbacks for the Living Spaces Barrio theme.
 */
class LivingSpacesBarrioTrustedCallbacks implements TrustedCallbackInterface {

  /**
   * Pre-render callback for dropbutton and operations elements.
   */
  public static function preRenderDropbutton($element) {

    // Prepare size and type classes for button.
    $button_size_class = '';
    if (!empty($element['#dropbutton_type'])) {
      switch ($element['#dropbutton_type']) {
        case 'small':
          $button_size_class = 'btn-sm';
          break;

        case 'large':
          $button_size_class = 'btn-lg';
          break;

      }
    }
    $button_type_class = 'btn-primary';
    if (!empty($element['#button_type'])) {
      $button_type_class = 'btn-' . $element['#button_type'];
    }

    // Prepare attributes for split button in dropdown.
    $element['#attributes']['type'] = 'button';
    $element['#attributes']['class'][] = 'btn';
    $element['#attributes']['class'][] = 'dropdown-toggle';
    $element['#attributes']['class'][] = 'dropdown-toggle-split';
    $element['#attributes']['data-bs-toggle'] = 'dropdown';
    $element['#attributes']['aria-expanded'] = 'false';
    $element['#attributes']['class'][] = $button_type_class;
    if (!empty($button_size_class)) {
      $element['#attributes']['class'][] = $button_size_class;
    }

    // Prepare attributes for dropdown menu and first button.
    if (!empty($element['#links'])) {
      $first = TRUE;
      foreach ($element['#links'] as $link) {
        $attributes = $link['url']->getOption('attributes') ?: [];

        // First item.
        if ($first) {
          $attributes['class'][] = 'btn';
          $attributes['class'][] = $button_type_class;
          if (!empty($button_size_class)) {
            $attributes['class'][] = $button_size_class;
          }
          $first = FALSE;
        }
        // Items in dropdown.
        else {
          $attributes['class'][] = 'dropdown-item';
        }
        $link['url']->setOption('attributes', $attributes);
      }
    }

    // Enable targeted theming of specific dropbuttons (e.g., 'operations' or
    // 'operations__node').
    if (isset($element['#subtype'])) {
      $element['#theme'] .= '__' . $element['#subtype'];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return [
      'preRenderDropbutton',
    ];
  }

}
