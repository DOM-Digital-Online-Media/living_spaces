<?php

namespace Drupal\living_spaces_event\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * LivingSpacesEventIcalController class.
 */
class LivingSpacesEventIcalController extends ControllerBase {

  /**
   * Returns the database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Returns the request_stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Returns the file_system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Returns the file.repository service.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * Constructs a new LivingSpacesEventIcalController object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Forward-compatibility shim for Symfony's RequestStack.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   Provides an interface for helpers that operate on files and stream wrappers.
   * @param \Drupal\file\FileRepositoryInterface $file_repository
   *   Performs file system operations and updates database records accordingly.
   */
  public function __construct(Connection $database, RequestStack $request, FileSystemInterface $file_system, FileRepositoryInterface $file_repository) {
    $this->database = $database;
    $this->request = $request;
    $this->fileSystem = $file_system;
    $this->fileRepository = $file_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('request_stack'),
      $container->get('file_system'),
      $container->get('file.repository')
    );
  }

  /**
   * Callback for 'iCal' route.
   */
  public function download(GroupInterface $group) {
    $host = $this->request->getCurrentRequest()->getHost();
    $calendar = new Calendar($host);

    $query = $this->database->select('living_spaces_event_field_data', 'efd');
    $query->fields('efd', ['id', 'label', 'description__value', 'location__value']);
    $query->condition('efd.space', $group->id());
    $query->innerJoin('living_spaces_event__field_start_date', 'efsd', 'efsd.entity_id = efd.id');
    $query->addField('efsd', 'field_start_date_value');
    $query->innerJoin('living_spaces_event__field_end_date', 'efed', 'efed.entity_id = efd.id');
    $query->addField('efed', 'field_end_date_value');

    if ($results = $query->execute()->fetchAllAssoc('id')) {
      foreach ($results as $result) {
        $event = new Event();
        $event->setDtStart(new \DateTime($result->field_start_date_value, new \DateTimeZone('UTC')));
        $event->setDtEnd(new \DateTime($result->field_end_date_value, new \DateTimeZone('UTC')));
        $event->setSummary($result->label);
        $event->setDescription($result->description__value);
        $event->setLocation($result->location__value);
        $event->setUrl( "{$host}/living-spaces-event/{$result->id}");
        $calendar->addComponent($event);
      }
    }

    $directory = 'public://ical';
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    $filename = "iCal {$group->label()}.ics";
    $uri = "{$directory}/{$filename}";

    $content = $calendar->render();
    try {
      $file = $this->fileRepository->writeData($content, $uri, FileSystemInterface::EXISTS_REPLACE);
    }
    catch (\Exception $e) {
      $this->messenger()->addWarning($e->getMessage());
      throw new NotFoundHttpException();
    }

    $parts = explode('://', $uri);
    $filepath = $this->fileSystem->realpath('public://') . '/' . $parts[1];

    if (!file_exists($filepath)) {
      $this->messenger()->addWarning($this->t('Cannot find iCal file.'));
      $this->loggerFactory->get('living_spaces_event')->error('Caanot find iCal file for @space', [
        '@space' => $group->label(),
      ]);
      throw new NotFoundHttpException();
    }

    $headers = [
      'Content-Type' => 'text/calendar',
      'Content-Disposition' => 'attachment; filename="' . $file->getFilename() . '"',
      'Content-Length' => $file->getSize(),
      'Content-Transfer-Encoding' => 'binary',
      'Pragma' => 'no-cache',
      'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
      'Expires' => '0',
      'Accept-Ranges' => 'bytes',
    ];

    return new BinaryFileResponse($uri, 200, $headers);
  }

  /**
   * Access callback for 'iCal' route.
   */
  public function access(GroupInterface $group) {
    $access = $this->currentUser()->hasPermission('access ical event export') ||
    $group->hasPermission('access ical event export', $this->currentUser());

    return $access ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
