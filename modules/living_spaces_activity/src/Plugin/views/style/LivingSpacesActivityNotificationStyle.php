<?php

namespace Drupal\living_spaces_activity\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Style plugin to render a list of notifications.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "living_spaces_activity_notification",
 *   title = @Translation("Notifications"),
 *   theme = "views_view_living_spaces_activity_notification",
 *   display_types = {"normal"}
 * )
 */
class LivingSpacesActivityNotificationStyle extends StylePluginBase {

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
