<?php

namespace Drupal\living_spaces_users\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Filter counselors or moderators for the current user.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("current_user_is_counselor_or_moderator")
 */
class Current extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();

    /** @var \Drupal\views_contextual_filters_or\Plugin\views\query\ExtendedSearchApiQuery $query */
    $query = $this->query;

    $current_user_id = \Drupal::currentUser()->id();
    $condition = ($query->createConditionGroup('OR'))
      ->addCondition('field_counselor', $current_user_id, '=')
      ->addCondition('field_moderators', $current_user_id, '=');

    $query->addWhere($this->options['group'], $condition);

  }

}
