<?php

namespace Drupal\living_spaces_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Block with 'LivingSpacesEventBaseInfoBlock' block.
 *
 * @Block(
 *   id = "living_spaces_event_base_info_block",
 *   admin_label = @Translation("Event base info"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "living_spaces_event" = @ContextDefinition("entity:living_spaces_event", required = TRUE, label = @Translation("Event"))
 *   }
 * )
 */
class LivingSpacesEventBaseInfoBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the date.formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormat;

  /**
   * Returns the current_user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a LivingSpacesEventBaseInfoBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_format
   *   Provides an interface defining a date formatter.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Defines an account interface which represents the current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatterInterface $date_format, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->dateFormat = $date_format;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('date.formatter'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    /** @var \Drupal\living_spaces_event\Entity\LivingSpaceEventInterface $event */
    $event = $this->getContextValue('living_spaces_event');

    if (!empty($event->in_preview)) {
      return $build;
    }

    $date = '';
    if ($event->hasField('field_start_date') && !$event->get('field_start_date')->isEmpty()) {
      $start = new DrupalDateTime($event->get('field_start_date')->getValue()[0]['value'], new \DateTimeZone('UTC'));
      $date = $start->format('D, d.m.Y - H:i', [
        'timezone' => $this->currentUser->getTimeZone(),
      ]);
    }

    if ($event->hasField('field_end_date') && !$event->get('field_end_date')->isEmpty()) {
      $end = new DrupalDateTime($event->get('field_end_date')->getValue()[0]['value'], new \DateTimeZone('UTC'));
      $date .= ' ' . $end->format('D, d.m.Y - H:i', [
        'timezone' => $this->currentUser->getTimeZone(),
      ]);
    }

    if ($date) {
      $build['when'] = [
        '#type' => 'markup',
        '#markup' => '<b>' . $this->t('When') . '</b>: ' . $date,
        '#prefix' => '<div class="when">',
        '#suffix' => '</div>',
        '#cache' => [
          'tags' => $event->getCacheTags(),
        ],
      ];
    }

    if (!$event->get('location')->isEmpty()) {
      $build['where'] = [
        '#type' => 'markup',
        '#markup' => '<b>' . $this->t('Where') . '</b>: ' . $event->get('location')->getValue()[0]['value'],
        '#prefix' => '<div class="where">',
        '#suffix' => '</div>',
        '#cache' => [
          'tags' => $event->getCacheTags(),
        ],
      ];
    }

    return $build;
  }

}
