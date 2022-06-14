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
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Decorator constructor.
   */
  public function __construct(GroupMembershipLoaderInterface $original, EntityTypeManagerInterface $entity_type_manager) {
    $this->originalService = $original;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function load(GroupInterface $group, AccountInterface $account) {
    $membership = $this->originalService->load($group, $account);

    if (!$membership && !$group->get('circles')->isEmpty()) {
      $circles = [];
      foreach ($group->get('circles')->getValue() as $item) {
        if (!empty($item['target_id'])) {
          $circles[] = $item['target_id'];
        }
      }

      $group_content_mamanger = $this->entityTypeManager->getStorage('group_content');
      $query = $group_content_mamanger->getQuery();
      $query->condition('gid', $circles, 'IN');
      $query->condition('entity_id', $account->id());
      $query->condition('gid.entity.type', 'circle');
      $query->condition('type', 'circle-group_membership');
      $query->accessCheck(FALSE);

      if ($query->execute()) {
        // Add fake membership.
        $group_membership = $group_content_mamanger->create([
          'type' => "{$group->bundle()}-group_membership",
          'gid' => $group->id(),
          'entity_id' => $account->id(),
        ]);

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

      $group_content_mamanger = $this->entityTypeManager->getStorage('group_content');
      $query = $group_content_mamanger->getQuery();
      $query->condition('gid', $circles, 'IN');
      $query->condition('gid.entity.type', 'circle');
      $query->condition('type', 'circle-group_membership');
      $query->accessCheck(FALSE);
      if ($roles) {
        $query->condition('group_roles', $roles);
      }

      if ($content = $query->execute()) {
        // Add fake memberships.
        foreach ($group_content_mamanger->loadMultiple($content) as $item) {
          $group_membership = $group_content_mamanger->create([
            'type' => "{$group->bundle()}-group_membership",
            'gid' => $group->id(),
            'entity_id' => $item->getEntity()->id(),
          ]);

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

    if ($account) {
      $group_content_mamanger = $this->entityTypeManager->getStorage('group_content');
      $group_manager = $this->entityTypeManager->getStorage('group');

      // Check if a user is a member of circle group type.
      $query = $group_content_mamanger->getQuery();
      $query->condition('entity_id', $account->id());
      $query->condition('gid.entity.type', 'circle');
      $query->condition('type', 'circle-group_membership');
      $query->accessCheck(FALSE);
      if ($roles) {
        $query->condition('group_roles', $roles, 'IN');
      }

      if ($content = $query->execute()) {
        foreach ($group_content_mamanger->loadMultiple($content) as $item) {
          $circle = $item->getGroup();

          $query = $group_manager->getQuery();
          $query->condition('circles', $circle->id());
          $query->accessCheck(FALSE);

          if ($groups = $query->execute()) {
            // Add fake memberships.
            foreach ($group_manager->loadMultiple($groups) as $group) {
              $group_membership = $group_content_mamanger->create([
                'type' => "{$group->bundle()}-group_membership",
                'gid' => $group->id(),
                'entity_id' => $account->id(),
              ]);

              $memberships[] = new GroupMembership($group_membership);
            }
          }
        }
      }
    }

    return $memberships;
  }

}
