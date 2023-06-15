<?php

namespace Drupal\living_spaces_circles\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;

/**
 * LivingSpacesCirclesAutoCompleteController class.
 */
class LivingSpacesCirclesAutoCompleteController extends ControllerBase {

  /**
   * Returns response for the autocomplete.
   */
  public function autocomplete(Request $request) {
    $results = [];
    $input = trim($request->query->get('q'));

    $group_manager = $this->entityTypeManager()->getStorage('group');

    $query = $group_manager->getQuery();
    $query->condition('type', 'circle');
    if ($input) {
      $query->condition('label', '%' . Xss::filter($input) . '%', 'LIKE');
    }
    $query->condition('status', TRUE);
    $query->range(0, 10);

    if ($groups = $query->execute()) {
      foreach ($group_manager->loadMultiple($groups) as $group) {
        $results["{$group->label()} ({$group->id()})"] = $group->label();
      }
    }

    return new JsonResponse($results);
  }

}
