<?php

namespace Drupal\living_spaces_event\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\core\form\FormStateInterface;

/**
 * Style plugin to render a list of events.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "living_spaces_event_week",
 *   title = @Translation("Week style"),
 *   theme = "views_view_living_spaces_event_week",
 *   display_types = {"normal"}
 * )
 */
class LivingSpaceEventWeekStyle extends StylePluginBase {

  /**
   * {@inheritdoc}
   */
  protected $usesGrouping = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['class'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wrapper class'),
      '#default_value' => $this->options['class'] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function evenEmpty() {
    return TRUE;
  }

}
