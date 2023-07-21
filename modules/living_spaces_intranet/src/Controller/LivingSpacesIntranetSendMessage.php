<?php

namespace Drupal\living_spaces_intranet\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\living_spaces_intranet\LivingSpacesIntranetManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
    $this->messenger()->addStatus('The message has been sent.');

    $url = Url::fromRoute('<front>');
    return new RedirectResponse($url->toString());
  }

}
