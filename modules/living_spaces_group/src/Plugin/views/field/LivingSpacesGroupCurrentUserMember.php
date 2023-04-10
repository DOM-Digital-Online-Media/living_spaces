<?php

namespace Drupal\living_spaces_group\Plugin\views\field;

use Drupal\Console\Bootstrap\Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * @ingroup views_field_handlers
 *
 * @ViewsField("living_spaces_group_current_user_member")
 */
class LivingSpacesGroupCurrentUserMember extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // We don't need to modify query for this particular example.
  }

  /**
   * {@inheritdoc}
   */
//  protected function defineOptions() {
//    $options = parent::defineOptions();
//    $options['relative_date_format'] = ['default' => 'd.m.Y'];
//    return $options;
//  }

  /**
   * {@inheritdoc}
   */
//  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
//    $form['relative_date_format'] = [
//      '#type' => 'textfield',
//      '#required' => TRUE,
//      '#title' => $this->t('Relative date format'),
//      '#description' => $this->t('This format will be used for dates older than 7 days.'),
//      '#default_value' => $this->options['relative_date_format'],
//    ];
//    parent::buildOptionsForm($form, $form_state);
//  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
//    $created = $values->node_field_data_created;
//    // Default result if conditions will fail.
//    $pub_date = date($this->options['relative_date_format'], $created);
//    if ($created >= strtotime('today')) {
//      $pub_date = $this->t('today');
//    }
//    else if ($created >= strtotime('-1 day')) {
//      $pub_date = $this->t('tomorrow');
//    }
//    else if ($created >= strtotime('-2 days')) {
//      $pub_date = $this->t('day before yesterday');
//    }
//    else if ($created >= strtotime('-3 days')) {
//      $pub_date = \Drupal::translation()->formatPlural(3, '@count day ago', '@count days ago', ['@count' => 3]);
//    }
//    else if ($created >= strtotime('-4 days')) {
//      $pub_date = \Drupal::translation()->formatPlural(4, '@count day ago', '@count days ago', ['@count' => 4]);
//    }
//    else if ($created >= strtotime('-5 days')) {
//      $pub_date = \Drupal::translation()->formatPlural(5, '@count day ago', '@count days ago', ['@count' => 5]);
//    }
//    else if ($created >= strtotime('-6 days')) {
//      $pub_date = \Drupal::translation()->formatPlural(6, '@count day ago', '@count days ago', ['@count' => 6]);
//    }
//    else if ($created >= strtotime('-7 days')) {
//      $pub_date = \Drupal::translation()->formatPlural(7, '@count day ago', '@count days ago', ['@count' => 7]);
//    }

//    return [
//      '#markup' => $pub_date,
//    ];

    $user = \Drupal::service('entity_type.manager')->getStorage('user')
      ->load(\Drupal::service('current_user')->id());
    $spaces = array_column($user->get('joined_spaces')->getValue(), 'target_id');
    return in_array($values->_entity->id(), $spaces);
  }


  /**
   * {@inheritdoc}
   */
//  public function clickSort($order) {
//    $this->query->addOrderBy('node_field_data', 'created', $order);
//  }

}
