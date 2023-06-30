<?php

namespace Drupal\living_spaces_circles;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\GroupMembershipLoaderInterface;
use Drupal\group\GroupMembership;

/**
 * Membership loader service decorator.
 */
class LivingSpacesCirclesMembershipLoader implements GroupMembershipLoaderInterface {

  /**
   * Decorated service.
   *
   * @var \Drupal\group\GroupMembershipLoaderInterface
   */
  protected $originalService;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user's account object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new GroupTypeController.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(GroupMembershipLoaderInterface $originan, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    $this->originalService = $originan;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function load(GroupInterface $group, AccountInterface $account) {
    $membership = $this->originalService->load($group, $account);
    $user = $this->entityTypeManager->getStorage('user')
      ->load($account->id());

    if (!$membership && !$group->get('circles')->isEmpty()) {
      $circles = [];
      foreach ($group->get('circles')->getValue() as $item) {
        if (!empty($item['target_id'])) {
          $circles[] = $item['target_id'];
        }
      }

      /** @var \Drupal\group\Entity\Storage\GroupRelationshipStorageInterface $relationship_storage */
      $relationship_storage = $this->entityTypeManager->getStorage('group_relationship');
      $query = $relationship_storage->getQuery();
      $query->condition('gid', $circles, 'IN');
      $query->condition('entity_id', $account->id());
      $query->condition('gid.entity.type', 'circle');
      $query->condition('plugin_id', 'group_membership');
      $query->accessCheck(FALSE);

      if ($query->execute()) {
        // Add fake membership.
        $group_membership = $relationship_storage->createForEntityInGroup($user, $group, 'group_membership');
        $membership = new GroupMembership($group_membership);
      }
    }

    return $membership;
  }

  /**
   * {@inheritdoc}
   */
  public function loadByGroup(GroupInterface $group, $roles = NULL) {
    $memberships = $this->originalService->loadByGroup($group, $roles);

    // Check if a group has circles.
    if (!$group->get('circles')->isEmpty()) {
      $circles = [];
      foreach ($group->get('circles')->getValue() as $item) {
        if (!empty($item['target_id'])) {
          $circles[] = $item['target_id'];
        }
      }

      /** @var \Drupal\group\Entity\Storage\GroupRelationshipStorageInterface $relationship_storage */
      $relationship_storage = $this->entityTypeManager->getStorage('group_relationship');
      $query = $relationship_storage->getQuery();
      $query->condition('gid', $circles, 'IN');
      $query->condition('gid.entity.type', 'circle');
      $query->condition('plugin_id', 'group_membership');
      $query->accessCheck(FALSE);
      if ($roles) {
        $query->condition('group_roles', $roles);
      }

      if ($relationships = $query->execute()) {
        // Add fake memberships.
        foreach ($relationship_storage->loadMultiple($relationships) as $item) {
          /** @var \Drupal\group\Entity\GroupRelationshipInterface $item */
          $group_membership = $relationship_storage->createForEntityInGroup($item->getEntity(), $group, 'group_membership');
          $memberships[] = new GroupMembership($group_membership);
        }
      }
    }

    return $memberships;
  }

  /**
   * {@inheritdoc}
   */
  public function loadByUser(AccountInterface $account = NULL, $roles = NULL) {
    $memberships = $this->originalService->loadByUser($account, $roles);

    /** @var \Drupal\group\Entity\Storage\GroupRelationshipStorageInterface $relationship_storage */
    $relationship_storage = $this->entityTypeManager->getStorage('group_relationship');
    $group_storage = $this->entityTypeManager->getStorage('group');
    $user = $this->entityTypeManager->getStorage('user')
      ->load($account ? $account->id() : $this->currentUser->id());

    // Check if a user is a member of circle group type.
    $query = $relationship_storage->getQuery();
    $query->condition('entity_id', $account->id());
    $query->condition('gid.entity.type', 'circle');
    $query->condition('plugin_id', 'group_membership');
    $query->accessCheck(FALSE);
    if ($roles) {
      $query->condition('group_roles', $roles, 'IN');
    }

    if ($content = $query->execute()) {
      $circles = [];

      /** @var \Drupal\group\Entity\GroupRelationshipInterface $item */
      foreach ($relationship_storage->loadMultiple($content) as $item) {
        $circles[] = $item->getGroupId();
      }

      $query = $group_storage->getQuery();
      $query->condition('circles', $circles, 'IN');
      $query->accessCheck(FALSE);

      if ($groups = $query->execute()) {
        // Add fake memberships.
        foreach ($group_storage->loadMultiple($groups) as $group) {
          /** @var \Drupal\group\Entity\GroupInterface $group */
          $group_membership = $relationship_storage->createForEntityInGroup($user, $group, 'group_membership');
          $memberships[] = new GroupMembership($group_membership);
        }
      }
    }

    return $memberships;
  }

}
