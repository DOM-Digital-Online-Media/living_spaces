<?php

namespace Drupal\living_spaces\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block to display access denied message.
 *
 * @Block(
 *   id = "living_spaces_access_denied_block",
 *   admin_label = @Translation("Access denied"),
 *   category = @Translation("Living Spaces"),
 * )
 */
class LivingSpacesAccessDeniedBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => $this->t('You are not authorized to access this page.'),
    ];
  }

}
