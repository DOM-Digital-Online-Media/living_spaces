<?php

namespace Drupal\living_spaces_intranet;

use GuzzleHttp\Client;
use Drupal\Component\Serialization\Json;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Manager for intranet related methods.
 */
class LivingSpacesIntranetManager implements LivingSpacesIntranetManagerInterface {

  /**
   * Returns the http_client service.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * LivingSpacesIntranetManager constructor.
   *
   * @param \GuzzleHttp\Client $http_client
   *   Provides a http client service.
   */
  public function __construct(Client $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessage($path, $data = [], $headers = []) {
    $content = [];

    try {
      $request = $this->httpClient->post($path, [
        'verify' => FALSE,
        'form_params' => $data,
        'headers' => $headers + [
          'Cache-Control' => 'no-cache',
        ],
      ]);

      $content = Json::decode($request->getBody()->getContents());
    }
    catch (GuzzleException $e) {
      watchdog_exception('living_spaces_intranet', $e);
    }

    return $content;
  }

}
