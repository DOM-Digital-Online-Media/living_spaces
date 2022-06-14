<?php

namespace Drupal\living_spaces_event\Plugin\FullcalendarViewProcessor;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\fullcalendar_view\Plugin\FullcalendarViewProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Http\RequestStack;

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
   * Returns the database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Returns the request_stack service.
   *
   * @var \Drupal\Core\Http\RequestStack
   */
  protected $request;

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
   * @param \Drupal\Core\Database\Connection $database
   *   Base Database API class.
   * @param \Drupal\Core\Http\RequestStack $request
   *   Forward-compatibility shim for Symfony's RequestStack.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $config, Connection $database, RequestStack $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config;
    $this->database = $database;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('config.factory'),
      $container->get('database'),
      $container->get('request_stack')
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
        foreach ($options['events'] as &$event) {
          $query = $this->database->select('living_spaces_event', 'lse');
          $query->fields('lse');
          $query->condition('lse.id', $event['eid']);
          $result = $query->execute()->fetchAssoc();

          $event['className'] = !empty($result['type']) ? $result['type'] : '';
        }

        $data['calendar_options'] = Json::encode($options);
      }
    }
  }

}
