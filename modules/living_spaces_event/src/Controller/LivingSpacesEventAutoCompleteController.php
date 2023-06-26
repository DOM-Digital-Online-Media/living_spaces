<?php

namespace Drupal\living_spaces_event\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;

/**
 * LivingSpacesEventAutoCompleteController class.
 */
class LivingSpacesEventAutoCompleteController extends ControllerBase {

  /**
   * Returns the database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new LivingSpacesEventAutoCompleteController object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Returns response for the autocomplete.
   */
  public function autocomplete(Request $request, $group) {
    $results = [];
    $input = $request->query->get('q');

    if (!$input) {
      return new JsonResponse($results);
    }

    $properties = [
      'group_type' => $this->entityTypeManager()->getStorage('group')->load($group)->bundle(),
      'content_plugin' => 'group_node:agenda',
    ];

    if ($relationship_type = $this->entityTypeManager()->getStorage('group_relationship_type')->loadByProperties($properties)) {
      $relationship_type = reset($relationship_type);

      $query = $this->database->select('node_field_data', 'n');
      $query->fields('n', ['nid', 'title']);
      $query->join('group_relationship_field_data', 'gc', 'n.nid = gc.entity_id AND gc.gid = :gid AND gc.type = :type', [
        ':gid' => $group,
        ':type' => $relationship_type->id(),
      ]);
      $query->leftJoin('living_spaces_event__agenda', 'a', 'n.nid = a.agenda_target_id');
      $query->isNull('a.agenda_target_id');
      $query->condition('n.title', '%' . Xss::filter($input) . '%', 'LIKE');
      $query->condition('n.status', 1);
      $query->range(0, 10);

      if ($nodes = $query->execute()->fetchAllKeyed()) {
        foreach ($nodes as $nid => $label) {
          $results[] = [
            'value' => "{$label} ({$nid})",
            'label' => $label,
          ];
        }
      }
    }

    return new JsonResponse($results);
  }

}
