<?php

namespace Drupal\living_spaces_event\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * LivingSpacesEventIcalController class.
 */
class LivingSpacesEventIcalController extends ControllerBase {

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
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Forward-compatibility shim for Symfony's RequestStack.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   Provides an interface for helpers that operate on files and stream wrappers.
   * @param \Drupal\file\FileRepositoryInterface $file_repository
   *   Performs file system operations and updates database records accordingly.
   */
  public function __construct(RequestStack $request, FileSystemInterface $file_system, FileRepositoryInterface $file_repository) {
    $this->request = $request;
    $this->fileSystem = $file_system;
    $this->fileRepository = $file_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('file_system'),
      $container->get('file.repository')
    );
  }

  /**
   * Callback for 'iCal' route.
   */
  public function download(GroupInterface $group) {
    // Convert the date to the Timezone of the user requesting.
    $start_date = '2023-10-18 10:00:00';
    $end_date = '2023-10-18 11:00:00';

    $host = $this->request->getCurrentRequest()->getHost();

    $calendar = new Calendar($host);

    $event = new Event();
    $event->setDtStart(new \DateTime($start_date));
    $event->setDtEnd(new \DateTime($end_date));
    $event->setSummary($group->label());
    $calendar->addComponent($event);

    $filename = "iCal-{$group->label()}.ics";
    $uri = 'public://' . $filename;

    $content = $calendar->render();
    $file = $this->fileRepository->writeData($content, $uri, FileSystemInterface::EXISTS_REPLACE);

    if (empty($file)) {
      return new Response(
        'Simple ICS Error, Please contact the System Administrator'
      );
    }

    $mimetype = 'text/calendar';
    $scheme = 'public';
    $parts = explode('://', $uri);
    $file_directory = $this->fileSystem->realpath($scheme . "://");
    $filepath = $file_directory . '/' . $parts[1];
    $filename = $file->getFilename();

    if (!file_exists($filepath)) {
      throw new NotFoundHttpException();
    }

    $headers = [
      'Content-Type' => $mimetype,
      'Content-Disposition' => 'attachment; filename="' . $filename . '"',
      'Content-Length' => $file->getSize(),
      'Content-Transfer-Encoding' => 'binary',
      'Pragma' => 'no-cache',
      'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
      'Expires' => '0',
      'Accept-Ranges' => 'bytes',
    ];

    return new BinaryFileResponse($uri, 200, $headers, $scheme !== 'private');
  }

  /**
   * Access callback for 'iCal' route.
   */
  public function access(GroupInterface $group) {
    return AccessResult::allowed();
  }

}
