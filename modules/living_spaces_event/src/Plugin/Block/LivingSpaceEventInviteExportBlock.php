<?php

namespace Drupal\living_spaces_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Link;

/**
 * Block with 'LivingSpaceEventInviteExportBlock' block.
 *
 * @Block(
 *   id = "living_spaces_event_invite_export_block",
 *   admin_label = @Translation("Event Invite export"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "living_spaces_event" = @ContextDefinition("entity:living_spaces_event", required = TRUE, label = @Translation("Event"))
 *   }
 * )
 */
class LivingSpaceEventInviteExportBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInterface $event */
    $event = $this->getContextValue('living_spaces_event');

    if (!empty($event->in_preview)) {
      return [];
    }

    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => Link::createFromRoute($this->t('Download participant list'), 'view.event_invite_export.rest_export', [
        'living_spaces_event' => $event->id(),
      ], [
        'attributes' => [
          'class' => ['ical-icon', 'btn', 'btn-default'],
        ],
      ])->toString(),
      '#prefix' => '<h2>' . $this->t('Participant') . '</h2>',
      '#cache' => [
        'tags' => $event->getCacheTags(),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    return $account->hasPermission('access event invite export') ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
