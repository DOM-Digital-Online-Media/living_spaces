<?php

namespace Drupal\living_spaces_users\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Provides a Send E-mail action.
 *
 * @Action(
 *   id = "living_spaces_users_send_e_mail",
 *   label = @Translation("Send E-mail"),
 *   type = "user",
 * )
 */
class LivingSpacesUsersSendMail extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $dispatcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dispatcher = $dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('event_dispatcher'));
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE): AccessResultInterface|bool {
    $access = AccessResult::allowedIfHasPermission($account, 'access send email action');
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $this->executeMultiple([$entity]);
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $mails = [];
    foreach ($entities as $entity) {
      $mails[] = $entity->getEmail();
    }

    $redirect = new RedirectResponse('mailto:' . implode(',', $mails));
    $listener = function ($event) use ($redirect) {
      $event->setResponse($redirect);
    };
    $this->dispatcher->addListener(KernelEvents::RESPONSE, $listener);
  }

}
