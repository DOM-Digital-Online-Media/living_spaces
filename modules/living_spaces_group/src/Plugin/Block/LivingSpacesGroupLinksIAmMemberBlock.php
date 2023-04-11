<?php

namespace Drupal\living_spaces_group\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides a links for member Spaces and Circles.
 *
 * @Block(
 *   id = "living_spaces_group_links_i_am_member",
 *   admin_label = @Translation("Links for member Spaces and Circles"),
 *   category = @Translation("Living Spaces"),
 * )
 */
class LivingSpacesGroupLinksIAmMemberBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['wrapper'] = [
      '#theme' => 'collapsible_links',
      '#title' => $this->t('I am member of'),
      '#active' => TRUE,
      '#collapsed' => FALSE,
    ];
    $build['wrapper']['#links'][] = [
      '#type' => 'link',
      '#title' => $this->t('Member of Spaces'),
      '#url' => Url::fromRoute('page_manager.page_view_i_am_member_of_spaces_i_am_member_of_spaces-layout_builder-0'),
    ];
    $build['wrapper']['#links'][] = [
      '#type' => 'link',
      '#title' => $this->t('Member of Circles'),
      '#url' => Url::fromRoute('page_manager.page_view_i_am_member_of_circles_i_am_member_of_circles-layout_builder-0'),
    ];
    return $build;
  }

}
