<?php

namespace Drupal\living_spaces_intranet;

/**
 * Interface for intranet manager service.
 */
interface LivingSpacesIntranetManagerInterface {

  /**
   * Send request to provided URL.
   *
   * @param string $path
   *   URL to send request.
   * @param array $data
   *   An array of post data.
   * @param array $headers
   *   An array of headers.
   *
   * @return array
   *   Response data array.
   */
  public function sendMessage($path, $data = [], $headers = []);

}
