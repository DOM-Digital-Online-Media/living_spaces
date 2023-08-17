<?php

namespace Drupal\living_spaces_sections\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for living space section add/edit forms.
 */
class LivingSpacesSectionForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    living_spaces_sections_section_form_alter($form, $this->entity);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);
    $form_state->setRedirect('entity.living_spaces_section.canonical', [
      'living_spaces_section' => $this->entity->id(),
    ]);
    return $status;
  }

}
