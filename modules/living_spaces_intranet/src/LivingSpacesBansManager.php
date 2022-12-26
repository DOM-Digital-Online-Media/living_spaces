<?php

namespace Drupal\living_spaces_intranet;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Manager for ban related methods.
 */
class LivingSpacesBansManager implements LivingSpacesBansManagerInterface {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the current_user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * LivingSpacesBansManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Defines an account interface which represents the current user.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserBans(AccountInterface $user, array $types = []): array {
    $storage = $this->entityTypeManager->getStorage('living_spaces_ban');
    $query = $storage->getQuery();
    $query->condition('target_user', $user->id());
    $query->condition('expire', $this->time->getRequestTime(), '>');

    if ($types) {
      $query->condition('type', $types, 'IN');
    }

    if ($results = $query->execute()) {
      return $storage->loadMultiple($results);
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function setUserBan(AccountInterface $user, array $data) {
    $bundle = !empty($data['bundle']) ? $data['bundle'] : 'global';

    if ($this->currentUser->hasPermission('administer ban') ||
      $this->currentUser->hasPermission("create {$bundle} ban")
    ) {
      $storage = $this->entityTypeManager->getStorage('living_spaces_ban');

      $ban = $storage->create([
        'type' => $bundle,
        'target_user' => $user->id(),
        'title' => !empty($data['reason']) ? $data['reason'] : "{$bundle} ban {$user->getDisplayName()}",
        'expire' => !empty($data['expire']) ? $data['expire'] : strtotime('+2 days'),
      ]);
      $ban->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteUserBans(AccountInterface $user, array $types = []) {
    if ($bans = $this->getUserBans($user, $types)) {
      $storage = $this->entityTypeManager->getStorage('living_spaces_ban');
      $storage->delete($bans);
    }
  }

}
