<?php

namespace Drupal\living_spaces_activity\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\views\Views;

/**
 * Class LivingSpacesActivityMarkMessagesController.
 */
class LivingSpacesActivityMarkMessagesController extends ControllerBase {

  /**
   * Returns the renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new LivingSpacesActivityMarkMessagesController object.
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
   * Mark all unread messages as read.
   */
  public function mark($user) {
    if ($user != $this->currentUser()->id()) {
      throw new AccessDeniedHttpException();
    }

    $response = new AjaxResponse();
    $entity_manager = $this->entityTypeManager();

    $query = $entity_manager->getStorage('message_template')->getQuery();
    $query->condition('category', LIVING_SPACES_ACTIVITY_PERSONAL);
    $query->accessCheck();

    if ($templates = $query->execute()) {
      $query = $entity_manager->getStorage('message')->getQuery();
      $query->condition('template', $templates, 'IN');
      $query->condition('uid', $user);
      $query->condition('is_read', FALSE);
      $query->accessCheck();

      if ($messages = $query->execute()) {
        /** @var \Drupal\message\MessageInterface $message */
        foreach ($entity_manager->getStorage('message')->loadMultiple($messages) as $message) {
          $message->set('is_read', TRUE);
          $message->save();
        }
      }
    }

    $view = Views::getView('message');
    $view->setDisplay('user_notifications');
    $view->setArguments([$user]);
    $output = $view->render();

    $output = $this->renderer->render($output);
    $response->addCommand(new ReplaceCommand('.js-view-dom-id-message-user_notifications', $output));

    $view = Views::getView('message');
    $view->setDisplay('my_notifications_page');
    $view->setArguments([$user]);
    $output = $view->render();

    $output = $this->renderer->render($output);
    $response->addCommand(new ReplaceCommand('.js-view-dom-id-message-my-notifications', $output));

    $counter = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => 0,
      '#attributes' => ['class' => ['notification-counter']],
    ];

    $counter = $this->renderer->render($counter);
    $response->addCommand(new ReplaceCommand('.notification-counter', $counter));

    $response->addCommand(new InvokeCommand('#space-activity-notifications .dropdown-toggle', 'dropdown', ['dispose']));
    $response->addCommand(new InvokeCommand('#space-activity-notifications .dropdown-toggle', 'dropdown', ['hide']));
    $response->addCommand(new InvokeCommand('#space-activity-notifications .dropdown-toggle', 'dropdown', ['show']));

    return $response;
  }

  /**
   * Mark all unread persistent messages as read.
   */
  public function markPersistent($user) {
    if ($user != $this->currentUser()->id()) {
      throw new AccessDeniedHttpException();
    }

    $response = new AjaxResponse();
    $entity_manager = $this->entityTypeManager();

    $query = $entity_manager->getStorage('message')->getQuery();
    $query->condition('template', 'persistent');
    $query->condition('uid', $user);
    $query->condition('is_read', FALSE);
    $query->accessCheck();

    if ($messages = $query->execute()) {
      /** @var \Drupal\message\MessageInterface $message */
      foreach ($entity_manager->getStorage('message')->loadMultiple($messages) as $message) {
        $message->set('is_read', TRUE);
        $message->save();
      }
    }

    $response->addCommand(new RemoveCommand('.view-id-message.view-display-id-persistent'));

    return $response;
  }

}
