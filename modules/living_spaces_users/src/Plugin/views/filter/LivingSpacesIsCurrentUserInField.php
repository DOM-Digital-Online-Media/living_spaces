<?php

namespace Drupal\living_spaces_users\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter fields list for the current user.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("living_spaces_is_current_user_in_field")
 */
class LivingSpacesIsCurrentUserInField extends FilterPluginBase {
  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['field_is_current_user'] = ['default' => NULL];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['field_is_current_user'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Field machine names'),
      '#description' => $this->t('Fields to check is contains current user. Each one should me on the new line.'),
      '#default_value' => $this->options['field_is_current_user'],
      '#required' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    parent::validateOptionsForm($form, $form_state);

    $option = 'field_is_current_user';
    if (empty(trim($form_state->getValues()['options'][$option]))) {
      $form_state->setError($form[$option], $this->t('Provided value cannot be empty.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();

    /** @var \Drupal\views_contextual_filters_or\Plugin\views\query\ExtendedSearchApiQuery $query */
    $query = $this->query;

    $current_user_id = $this->currentUser->id();
    $condition = ($query->createConditionGroup('OR'));
    foreach (explode(PHP_EOL, $this->options['field_is_current_user']) as $field) {
      $field = trim($field);
      if (!empty($field)) {
        $condition->addCondition($field, $current_user_id, '=');
      }
    }
    $query->addWhere($this->options['group'], $condition);

  }

}
