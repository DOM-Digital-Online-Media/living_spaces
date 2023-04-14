<?php

namespace Drupal\living_spaces_group\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A handler to provide display for membership.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("living_spaces_group_current_user_member")
 */
class LivingSpacesGroupCurrentUserMember extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $user = \Drupal::service('entity_type.manager')->getStorage('user')
      ->load(\Drupal::service('current_user')->id());
    $spaces = array_column($user->get('joined_spaces')->getValue(), 'target_id');
    return in_array($values->_entity->id(), $spaces);
  }

}
