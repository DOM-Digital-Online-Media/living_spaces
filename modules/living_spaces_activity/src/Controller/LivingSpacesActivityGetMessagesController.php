<?php

namespace Drupal\living_spaces_activity\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * LivingSpacesActivityGetMessagesController class.
 */
class LivingSpacesActivityGetMessagesController extends ControllerBase {

  /**
   * Returns the renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a LivingSpacesActivityGetMessagesController object.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Defines an interface for turning a render array into a string.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Callback for 'get persistent messages' route.
   */
  public function getPersistent($user) {
    if ($user != $this->currentUser()->id() || !views_get_view_result('message', 'persistent', $user)) {
      return new JsonResponse([
        'success' => FALSE,
      ]);
    }

    if ($block = $this->entityTypeManager()->getStorage('block')->load('views_block__message_persistent')) {
      $render = $this->entityTypeManager()->getViewBuilder('block')->view($block);

      return new JsonResponse([
        'success' => TRUE,
        'message' => $this->renderer->render($render),
      ]);
    }

    return new JsonResponse([
      'success' => FALSE,
    ]);
  }

}
