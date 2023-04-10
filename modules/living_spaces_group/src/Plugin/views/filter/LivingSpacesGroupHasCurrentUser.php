<?php

namespace Drupal\living_spaces_group\Plugin\views\filter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Filter handler to joined spaces.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("living_spaces_group_has_current_user")
 */
class LivingSpacesGroupHasCurrentUser extends FilterPluginBase {


//  /**
//   * Views Handler Plugin Manager.
//   *
//   * @var \Drupal\views\Plugin\ViewsHandlerManager
//   */
//  protected ViewsHandlerManager $joinHandler;

  /**
   * ExampleViewsFilter constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\views\Plugin\ViewsHandlerManager $join_handler
   */
//  public function __construct(array $configuration, $plugin_id, $plugin_definition, ViewsHandlerManager $join_handler) {
//    parent::__construct($configuration, $plugin_id, $plugin_definition);
//    $this->joinHandler = $join_handler;
//  }

  /**
   * {@inheritdoc}
   */
//  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ExampleViewsFilter {
//    return new static(
//      $configuration, $plugin_id, $plugin_definition,
//      $container->get('plugin.manager.views.join')
//    );
//  }

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  protected function valueForm(&$form, \Drupal\Core\Form\FormStateInterface $form_state): void {
    if ($form_state->get('exposed')) {
      $form['value'] = [
        '#title' => $this->t('Has current user'),
        '#type' => 'select',
        '#default_value' => 'any',
        '#options' => [
          'yes' => $this->t('Yes'),
          'no' => $this->t('No'),
        ],
      ];
    }
  }

  protected function valueSubmit($form, FormStateInterface $form_state) {
    // Drupal's FAPI system automatically puts '0' in for any checkbox that
    // was not set, and the key to the checkbox if it is set.
    // Unfortunately, this means that if the key to that checkbox is 0,
    // we are unable to tell if that checkbox was set or not.

    // Luckily, the '#value' on the checkboxes form actually contains
    // *only* a list of checkboxes that were set, and we can use that
    // instead.

    $form_state->setValue(['options', 'value'], $form['value']['#value']);
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['expose']['contains']['reduce'] = ['default' => FALSE];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['has_current_user'] = [
      '#title' => $this->t('Has current user'),
      '#type' => 'select',
      '#default_value' => $this->options['has_current_user'],
      '#options' => [
        '' => $this->t('All'),
        'yes' => $this->t('Yes'),
        'no' => $this->t('No'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query(): void {
    $this->ensureMyTable();

    $id = $this->options['expose']['identifier'];
    $input = $this->view->getExposedInput();

    $value = $this->options['has_current_user'] ?: ($input[$id] ?? NULL);
    if ($value) {
      switch ($value) {
        case 'yes':
          $operator = '=';
          break;

        default:
          $operator = '<>';
      }

      $this->query->addWhere(
        $this->options['group'],
        $this->tableAlias . '.' . $this->realField,
        \Drupal::service('current_user')->id(),
        $operator
      );

    }

  }

}
