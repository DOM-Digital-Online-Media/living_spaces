<?php

namespace Drupal\living_spaces_group\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an user view footer title block.
 *
 * @Block(
 *   id = "living_spaces_group_user_footer_title",
 *   admin_label = @Translation("User view footer title"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *   }
 * )
 */
class LivingSpacesGroupUserFooterTitleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\user\UserInterface $user */
    $user = $this->getContextValue('user');
    $build['content'] = [
      '#type' => 'html_tag',
      '#tag' => 'h2',
      '#value' => $this->t('@user is a member here', [
        '@user' => $user->getDisplayName(),
      ]),
    ];
    return $build;
  }

}
