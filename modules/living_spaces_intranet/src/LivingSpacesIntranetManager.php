<?php

namespace Drupal\living_spaces_intranet;

use WebSocket\Server;

/**
 * Manager for intranet related methods.
 */
class LivingSpacesIntranetManager implements LivingSpacesIntranetManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function sendMessage($message) {
    $options = [
      'return_obj' => TRUE,
      'timeout' => 30,
    ];

    try {
      $server = new Server($options);
      $server->accept();

      $server->text($message);
      $server->close();
    }
    catch (\Exception $e) {
      watchdog_exception('living_spaces_intranet', $e);
    }
  }

}
