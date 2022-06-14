<?php

namespace Drupal\living_spaces_protected_area\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Http\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\universal_device_detection\Detector\DefaultDetector;

/**
 * Form handler for 'living_spaces_protected_area.protected_area' route.
 */
class LivingSpacesProtectedAreaForm extends FormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the request_stack service.
   *
   * @var \Drupal\Core\Http\RequestStack
   */
  protected $request;

  /**
   * Returns the date.formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormat;

  /**
   * Returns the universal_device_detection.default service.
   *
   * @var \Drupal\universal_device_detection\Detector\DefaultDetector
   */
  protected $deviceDetection;

  /**
   * Constructs a LivingSpacesProtectedAreaForm form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Http\RequestStack $request
   *   Forward-compatibility shim for Symfony's RequestStack.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_format
   *   Provides an interface defining a date formatter.
   * @param \Drupal\universal_device_detection\Detector\DefaultDetector $device_detection
   *   Provides a device detector.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request, DateFormatterInterface $date_format, DefaultDetector $device_detection) {
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request;
    $this->dateFormat = $date_format;
    $this->deviceDetection = $device_detection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('date.formatter'),
      $container->get('universal_device_detection.default')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_protected_area_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $uuid = $this->request->getCurrentRequest()->query->get('uuid');
    $data = explode(':', $uuid);

    if (!empty($data[0]) && !empty($data[1]) && $entities = $this->entityTypeManager->getStorage($data[0])->loadByProperties(['uuid' => $data[1]])) {
      switch ($data[0]) {
        case 'living_spaces_access_area':
          $form['#attached']['library'][] = 'living_spaces_protected_area/fingerprint';
          $entity = reset($entities);

          $form['entity'] = [
            '#type' => 'hidden',
            '#value' => $entity->id(),
          ];

          $form['browser_key'] = [
            '#type' => 'hidden',
            '#default_value' => '',
          ];

          $form['message'] = [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#value' => $this->t('To access this page, this device requires active activation by an administrator.<br />
          To do this, click the <b>APPLY ACCESS</b> button.
          Your administrator can then activate the access for the device.<br />Please note that separate activations may be required for other possibly protected pages in the intranet.<br /><br />'),
          ];

          $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('APPLY ACCESS'),
          ];
          break;

        case 'living_spaces_access_grant':
          $entity = reset($entities);

          $form['message'] = [
            '#theme' => 'table',
            '#prefix' => $this->t('Access to this area has already been requested for this device:'),
            '#suffix' => $this->t('You will see this message until your administrator enables access for the currently used device.'),
            '#header' => [$this->t('Date'), $this->t('for browser')],
            '#rows' => [
              [
                $this->dateFormat->format($entity->get('created')->getString()),
                $entity->get('browser_name')->getString(),
              ],
            ],
          ];
          break;

      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $device = $this->deviceDetection->detect(FALSE);

    $browser_name = $device['info']['client']['name'];
    $browser_name .= !empty($device['info']['client']['version']) ? ' (' . $device['info']['client']['version'] . ')' : '';

    $os = $device['info']['os']['name'];
    $os .= !empty($device['info']['os']['version']) ? ' (' . $device['info']['os']['version'] . ')' : '';

    $entity = $this->entityTypeManager->getStorage('living_spaces_access_grant')->create([
      'access_area' => $form_state->getValue('entity'),
      'uid' => $this->currentUser()->id(),
      'status' => 0,
      'browser_key' => $form_state->getValue('browser_key'),
      'browser_name' => $browser_name,
      'os' => $os,
    ]);
    $entity->save();

    $message = $this->t('The request for access to this protected area has been sent successfully. As soon as an administrator has confirmed your request, your browser will automatically be granted access the next time you visit this page.');
    $this->messenger()->addStatus($message);
    $form_state->setRedirect('<front>');
  }

}
