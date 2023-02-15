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
  public function getMessage() {
    $message = '';

    try {
      $server = new Server();
      $server->accept();

      $message = $server->receive();
      $server->close();
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
    try {
      $server = new Server();
      $server->accept();

      $server->text($message);
      $server->close();
    }
    catch (\Exception $e) {
      watchdog_exception('living_spaces_intranet', $e);
    }
  }

}
