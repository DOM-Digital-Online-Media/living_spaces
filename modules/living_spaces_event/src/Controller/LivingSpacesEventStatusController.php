<?php

namespace Drupal\living_spaces_event\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\living_spaces_event\Entity\LivingSpaceEventInviteInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Url;

/**
 * LivingSpacesEventStatusController class.
 */
class LivingSpacesEventStatusController extends ControllerBase {

  /**
   * Returns the request_stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a LivingSpacesEventStatusController object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack that controls the lifecycle of requests.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * Callback for 'change event status' route.
   */
  public function changeStatus(LivingSpaceEventInviteInterface $living_spaces_event_invite, $status) {
    if ($status == 'delete') {

      /** @var \Drupal\message\Entity\Message $message */
      $message = $this->entityTypeManager()->getStorage('message')->create([
        'template' => 'user_removed_from_invite_list',
        'uid' => $living_spaces_event_invite->get('uid')->first()->getValue(),
        'field_event' => $living_spaces_event_invite->get('event')->first()->getValue(),
      ]);
      $message->save();

      $living_spaces_event_invite->delete();

    }
    else {
      $living_spaces_event_invite->set('status', $status);
      if (
        ($current_status = $living_spaces_event_invite->get('status')->entity) &&
        $current_status->uuid() == LIVING_SPACES_EVENT_ACCEPTED_STATUS &&
        ($inviter = $living_spaces_event_invite->get('inviter')->first()) &&
        $living_spaces_event_invite->get('uid')->first()->target_id == $this->currentUser()->id()
      ) {

        /** @var \Drupal\message\Entity\Message $message */
        $message = $this->entityTypeManager()->getStorage('message')->create([
          'template' => 'user_sent_event_invitation',
          'uid' => $inviter->getValue(),
          'field_event' => $living_spaces_event_invite->get('event')->first()->getValue(),
          'field_invited_user' => $this->currentUser()->id(),
        ]);
        $message->save();

      }
      $living_spaces_event_invite->save();

    }

    $this->messenger()->addStatus($this->t('The status has been changed.'));
    $query = $this->requestStack->getCurrentRequest()->query;

    if ($query->has('destination')) {
      return new RedirectResponse($query->get('destination'));
    }

    $url = Url::fromRoute('<front>');
    return new RedirectResponse($url->toString());
  }

  /**
   * Access callback for 'change event status' route.
   */
  public function access(LivingSpaceEventInviteInterface $living_spaces_event_invite, $status) {
    $access = $this->currentUser()->hasPermission('administer living spaces event invite') ||
      $this->currentUser()->id() == $living_spaces_event_invite->getOwnerId();

    return $access ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
