<?php

namespace Drupal\living_spaces_intranet;

/**
 * Interface for intranet manager service.
 */
interface LivingSpacesIntranetManagerInterface {

  /**
   * Send message to websocket.
   *
   * @param string $message
   *   Message to send.
   */
  public function sendMessage($message);

}
