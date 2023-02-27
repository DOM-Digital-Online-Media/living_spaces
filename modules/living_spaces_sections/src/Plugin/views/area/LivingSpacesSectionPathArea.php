<?php

namespace Drupal\living_spaces_sections\Plugin\views\area;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\area\AreaPluginBase;

/**
 * Views area section path handler.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("section_path")
 */
class LivingSpacesSectionPathArea extends AreaPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['bundle'] = ['default' => ''];
    $options['title'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $options = [];
    foreach (\Drupal::service('entity_type.bundle.info')->getBundleInfo('living_spaces_section') as $bundle => $info) {
      $options[$bundle] = $info['label'];
    }

    $form['bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Bundle'),
      '#options' => $options,
      '#empty_option' => $this->t('Select'),
      '#empty_value' => '',
      '#default_value' => $this->options['bundle'],
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->options['title'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    if (!$empty || !empty($this->options['empty'])) {
      if (!empty($this->options['bundle']) && !empty($this->options['title'])) {
        $gid = $this->view->args;
        if (is_numeric($gid[0]) && $space = \Drupal::entityTypeManager()->getStorage('group')->load($gid[0])) {
          if ($section = \Drupal::service('living_spaces_sections.manager')->getSectionFromGroupByType($space, $this->options['bundle'])) {
            return $section->toLink($this->options['title'])->toRenderable();
          }
        }
      }

    }

    return [];
  }

}
