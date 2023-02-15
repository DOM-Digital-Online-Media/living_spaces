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
    $server = new Server();
    $server->accept();

    $server->text($message);
    $server->close();
  }

}
