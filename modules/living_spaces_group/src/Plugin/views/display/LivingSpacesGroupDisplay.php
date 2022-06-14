<?php

namespace Drupal\living_spaces_group\Plugin\views\display;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\Block\ViewsBlock;
use Drupal\views\Plugin\views\display\Block;
use Drupal\group\Entity\GroupType;

/**
 * A block plugin that allows exposed filters to be configured.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "living_spaces_group_display",
 *   title = @Translation("User Group Display"),
 *   help = @Translation("Display the view as a block with group type exposed filter."),
 *   theme = "views_view",
 *   register_theme = FALSE,
 *   uses_hook_block = TRUE,
 *   contextual_links_locations = {"block"},
 *   admin = @Translation("User Group Display")
 * )
 *
 * @see \Drupal\views\Plugin\block\block\ViewsBlock
 * @see \Drupal\views\Plugin\Derivative\ViewsBlock
 */
class LivingSpacesGroupDisplay extends Block {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['allow']['contains']['exposed_type_filter'] = ['default' => 'exposed_type_filter'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSettings(array $settings) {
    $settings = parent::blockSettings($settings);

    $settings['exposed_type_filter'] = [
      'user_group_types' => [
        'value' => [],
      ],
    ];

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    parent::optionsSummary($categories, $options);
    $allow = array_filter($this->getOption('allow'));

    $allowed = [];
    if (isset($allow['items_per_page'])) {
      $allowed[] = $this->t('Items per page');
    }
    if (isset($allow['exposed_type_filter'])) {
      $allowed[] = $this->t('Exposed type filter');
    }

    $options['allow'] = [
      'category' => 'block',
      'title' => $this->t('Allow settings'),
      'value' => empty($allowed) ? $this->t('None') : implode(', ', $allowed),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm(ViewsBlock $block, array &$form, FormStateInterface $form_state) {
    parent::blockForm($block, $form, $form_state);
    $allow = array_filter($this->getOption('allow'));

    if (!empty($allow['exposed_type_filter'])) {
      $configuration = $block->getConfiguration();

      $options = [];
      foreach (GroupType::loadMultiple() as $type) {
        $options[$type->id()] = $type->label();
      }

      $form['override']['exposed_type_filter']['user_group_types']['value'] = [
        '#title' => $this->t('Group type'),
        '#type' => 'checkboxes',
        '#options' => $options,
        '#default_value' => $configuration['exposed_type_filter']['user_group_types']['value'],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit(ViewsBlock $block, $form, FormStateInterface $form_state) {
    parent::blockSubmit($block, $form, $form_state);

    $overides = $form_state->getValue(['override']);
    $config = $block->getConfiguration();

    if (!empty($overides['exposed_type_filter']['user_group_types']['value'])) {
      $config['exposed_type_filter']['user_group_types']['value'] = $overides['exposed_type_filter']['user_group_types']['value'];
    }
    elseif (isset($config['exposed_type_filter']['user_group_types'])) {
      unset($config['exposed_type_filter']['user_group_types']);
    }

    $form_state->unsetValue([
      'override',
      'exposed_type_filter',
      'user_group_types',
    ]);
    $block->setConfiguration($config);
  }

  /**
   * {@inheritdoc}
   */
  public function preBlockBuild(ViewsBlock $block) {
    $config = $block->getConfiguration();

    if (!empty($config['exposed_type_filter']['user_group_types'])) {
      $input = [];
      $input['user_group_types'] = $config['exposed_type_filter']['user_group_types']['value'];
      $this->view->exposed_data = $input;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function usesExposed() {
    if ($this->ajaxEnabled()) {
      return parent::usesExposed();
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    if ($form_state->get('section') == 'allow') {
      $form['allow']['#options']['exposed_type_filter'] = $this->t('Exposed type filter');
    }
  }

}
