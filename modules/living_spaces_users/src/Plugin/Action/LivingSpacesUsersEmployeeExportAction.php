<?php

namespace Drupal\living_spaces_users\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Provides LivingSpacesUsersEmployeeExportAction action.
 *
 * @Action(
 *   id = "living_spaces_users_employee_export_action",
 *   label = @Translation("Export data"),
 *   type = "user",
 * )
 */
class LivingSpacesUsersEmployeeExportAction extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a LivingSpacesUsersEmployeeExportAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Stores runtime messages sent out to individual users on the page.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MessengerInterface $messenger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $uids = [];

    foreach ($entities as $entity) {
      $uids[] = $entity->id();
    }

    $url = Url::fromRoute('view.users.employee_export', [], ['query' => ['uids' => implode('+', $uids)]])->toString();
    $message = $this->t('Export complete. Download the file <a download href=":download_url"  data-download-enabled="false" id="vde-automatic-download">here</a>.', [':download_url' => $url]);
    $this->messenger->addMessage($message);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    $this->executeMultiple([$object]);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $user = $account ? $account : $this->currentUser;
    return $user->hasPermission('manage employee profiles');
  }

}
