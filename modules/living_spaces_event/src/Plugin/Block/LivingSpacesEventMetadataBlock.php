<?php

namespace Drupal\living_spaces_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Block with 'LivingSpacesEventMetadataBlock' block.
 *
 * @Block(
 *   id = "living_spaces_event_metadata_block",
 *   admin_label = @Translation("Event metadata"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "living_spaces_event" = @ContextDefinition("entity:living_spaces_event", required = TRUE, label = @Translation("Event"))
 *   }
 * )
 */
class LivingSpacesEventMetadataBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the date.formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormat;

  /**
   * Constructs a LivingSpacesEventMetadataBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_format
   *   Provides an interface defining a date formatter.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatterInterface $date_format) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->dateFormat = $date_format;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('date.formatter')
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

    $build['metadata'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Created by @author on @date.', [
        '@author' => $event->getOwner()->toLink($event->getOwner()->getDisplayName())->toString(),
        '@date' => $this->dateFormat->format($event->get('created')->getString(), 'custom', 'l, d F Y - H:i'),
      ]),
      '#prefix' => '<div class="metadata">',
      '#suffix' => '</div>',
      '#cache' => [
        'tags' => $event->getCacheTags(),
      ],
    ];

    return $build;
  }

}
