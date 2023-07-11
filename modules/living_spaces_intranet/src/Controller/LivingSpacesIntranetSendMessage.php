<?php

namespace Drupal\living_spaces_intranet\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\living_spaces_intranet\LivingSpacesIntranetManagerInterface;

/**
 * LivingSpacesIntranetSendMessage class.
 */
class LivingSpacesIntranetSendMessage extends ControllerBase {

  /**
   * Returns the living_spaces_intranet.manager service.
   *
   * @var \Drupal\living_spaces_intranet\LivingSpacesIntranetManagerInterface
   */
  protected $websocketManager;

  /**
   * Constructs a LivingSpacesIntranetSendMessage object.
   *
   * @param \Drupal\living_spaces_intranet\LivingSpacesIntranetManagerInterface $renderer
   *   Interface for intranet manager service.
   */
  public function __construct(LivingSpacesIntranetManagerInterface $websocket_manager) {
    $this->websocketManager = $websocket_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('living_spaces_intranet.manager')
    );
  }

  /**
   * Callback for 'get persistent messages' route.
   */
  public function send($message) {
    $this->websocketManager->sendMessage($message);

    return new JsonResponse([
      'success' => TRUE,
      'message' => $this->t('The message has been sent.'),
    ]);
  }

}
