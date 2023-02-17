<?php

namespace Drupal\living_spaces_intranet;

use Drupal\Core\Site\Settings;
use WebSocket\Client;

/**
 * Manager for intranet related methods.
 */
class LivingSpacesIntranetManager implements LivingSpacesIntranetManagerInterface {

  /**
   * Returns the settings service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Returns the websocket path.
   *
   * @var string
   */
  protected $path;

  /**
   * LivingSpacesIntranetManager constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   Read only settings that are initialized with the class.
   */
  public function __construct(Settings $settings) {
    $this->settings = $settings;
    $this->path = (string) $this->settings->get('living_spaces_websocket_path', '');
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage() {
    $message = '';

    if (empty($this->path)) {
      return '';
    }

    try {
      $client = new Client($this->path);

      $message = $client->receive();
      $client->close();
    }
    catch (\Exception $e) {
      watchdog_exception('living_spaces_intranet', $e);
    }

    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessage($message) {
    if (empty($this->path)) {
      return;
    }

    try {
      $client = new Client($this->path);

      $client->text($message);
      $client->close();
    }
    catch (\Exception $e) {
      watchdog_exception('living_spaces_intranet', $e);
    }
  }

}
