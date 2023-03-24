<?php

namespace Drupal\living_spaces_event\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;

/**
 * LivingSpacesEventInviteAutoCompleteController class.
 */
class LivingSpacesEventInviteAutoCompleteController extends ControllerBase {

  /**
   * Returns response for the autocomplete.
   */
  public function autocomplete(Request $request) {
    $results = [];
    $input = $request->query->get('q');

    if (!$input) {
      return new JsonResponse($results);
    }

    $user_manager = $this->entityTypeManager()->getStorage('user');

    $query = $user_manager->getQuery();
    $query->condition('name', '%' . Xss::filter($input) . '%', 'LIKE');
    $query->condition('status', TRUE);
    $query->range(0, 10);

    if ($users = $query->execute()) {
      /** @var \Drupal\user\UserInterface $user */
      foreach ($user_manager->loadMultiple($users) as $user) {
        $name = $user->getDisplayName();
        $results[] = [
          'value' => "{$name} ({$user->id()})",
          'label' => $name,
        ];
      }
    }

    return new JsonResponse($results);
  }

}
