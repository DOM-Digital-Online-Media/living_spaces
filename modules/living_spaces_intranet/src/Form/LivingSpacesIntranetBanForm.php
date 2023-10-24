<?php

namespace Drupal\living_spaces_intranet\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Form handler for ban user action.
 */
class LivingSpacesIntranetBanForm extends FormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the tempstore.private service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Constructs a LivingSpacesIntranetBanForm form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   Creates a PrivateTempStore object for a given collection.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PrivateTempStoreFactory $temp_store_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->tempStoreFactory = $temp_store_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'living_spaces_intranet_ban_user_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $temp = $this->tempStoreFactory
      ->get('living_spaces_intranet_ban_user')
      ->get($this->currentUser()->id());

    $options = [];

    $form['type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Type'),
      '#options' => $options,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
