<?php

namespace Drupal\living_spaces_intranet;

/**
 * Interface for intranet manager service.
 */
interface LivingSpacesIntranetManagerInterface {

  /**
   * Send message to websocket.
   *
   * @return string|null
   *   Message from the server.
   */
  public function getMessage();

  /**
   * Send message to websocket.
   *
   * @param string $message
   *   Message to send.
   */
  public function sendMessage($message);

}
