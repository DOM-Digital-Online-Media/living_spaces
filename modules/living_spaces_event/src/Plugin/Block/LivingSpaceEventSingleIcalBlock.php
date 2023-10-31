<?php

namespace Drupal\living_spaces_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Link;

/**
 * Block with 'LivingSpaceEventSingleIcalBlock' block.
 *
 * @Block(
 *   id = "living_spaces_event_single_ical_block",
 *   admin_label = @Translation("Single iCal"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "living_spaces_event" = @ContextDefinition("entity:living_spaces_event", required = TRUE, label = @Translation("Event"))
 *   }
 * )
 */
class LivingSpaceEventSingleIcalBlock extends BlockBase {

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
      '#value' => Link::createFromRoute($this->t('Export calendar file'), 'living_spaces_event.ical_event', [
        'living_spaces_event' => $event->id(),
      ], [
        'attributes' => [
          'class' => ['ical-icon', 'btn', 'btn-default'],
        ],
      ])->toString(),
      '#prefix' => '<h2>' . $this->t('Include an appointment in your own calendar') . '</h2>',
      '#cache' => [
        'tags' => $event->getCacheTags(),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    $access = $account->hasPermission('access ical event export');

    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInterface $event */
    $event = $this->getContextValue('living_spaces_event');
    if (!$access && !$event->get('space')->isEmpty()) {
      $event->space->entity->hasPermission('access ical event export', $this->currentUser());
    }

    return $access ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
