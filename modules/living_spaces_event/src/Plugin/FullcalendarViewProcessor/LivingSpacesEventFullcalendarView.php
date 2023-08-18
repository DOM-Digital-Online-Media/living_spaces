<?php

namespace Drupal\living_spaces_event\Plugin\FullcalendarViewProcessor;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\fullcalendar_view\Plugin\FullcalendarViewProcessorInterface;
use Drupal\living_spaces_event\Entity\LivingSpaceEventInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Fullcalendar view processor plugins.
 *
 * @FullcalendarViewProcessor(
 *   id = "living_spaces_event_fullcalendar_view",
 *   label = @Translation("Fullcalendar view"),
 * )
 */
class LivingSpacesEventFullcalendarView extends PluginBase implements FullcalendarViewProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * Returns the config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Constructs a LivingSpacesEventFullcalendarView object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Defines the configuration object factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $config) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process(array &$variables) {
    if ('calendar' == $variables['view_id']) {
      $configs = $this->config->getEditable('living_spaces_event.calendar_filters');
      foreach ($variables['#attached']['drupalSettings']['fullCalendarView'] as &$data) {
        $data['calendar_filters'] = [];
        foreach ($configs->getRawData() as $key => $value) {
          if ($value) {
            $data['calendar_filters'][$key] = $value;
          }
        }

        $options = Json::decode($data['calendar_options']);
        foreach ($options['events'] ?? [] as $i => &$event) {
          $entity = $variables['rows'][$i]->_entity ?? NULL;
          if ($entity instanceof LivingSpaceEventInterface) {
            $event['className'] = $entity->bundle();
          }
        }
        $data['calendar_options'] = Json::encode($options);
      }
    }
  }

}
