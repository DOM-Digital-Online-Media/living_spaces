<?php

namespace Drupal\living_spaces_sections\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides block with living spaces section actions.
 *
 * @Block(
 *   id = "living_spaces_section_actions_block",
 *   admin_label = @Translation("Living Spaces section actions"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "section" = @ContextDefinition("entity:living_spaces_section", required = TRUE, label = @Translation("Living Spaces section"))
 *   }
 * )
 */
class LivingSpacesSectionActionsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface $section */
    $section = $this->getContextValue('section');

    return [
      '#theme' => 'dropdown',
      '#button_class' => 'btn-primary',
      '#button' => $this->t('- Select action -'),
      '#links' => $section->getSectionActions(),
    ];
  }

}
