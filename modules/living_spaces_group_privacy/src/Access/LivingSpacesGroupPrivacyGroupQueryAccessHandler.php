<?php

namespace Drupal\living_spaces_group_privacy\Access;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity\QueryAccess\ConditionGroup;
use Drupal\group\Entity\Access\GroupQueryAccessHandler;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Access\CalculatedGroupPermissionsItemInterface as CGPII;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controls query access for group entities.
 */
class LivingSpacesGroupPrivacyGroupQueryAccessHandler extends GroupQueryAccessHandler {

  /**
   * Returns the database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    /** @var static $instance */
    $instance = parent::createInstance($container, $entity_type);
    $instance->database = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildConditions($operation, AccountInterface $account) {
    $conditions = new ConditionGroup('OR');

    // If the account can bypass group access, we do not alter the query at all.
    $conditions->addCacheContexts(['user.permissions']);
    if ($account->hasPermission('bypass group access')) {
      return $conditions;
    }

    $permission = $this->getPermissionName($operation);
    $conditions->addCacheContexts(['user.group_permissions']);

    $calculated_permissions = $this->groupPermissionCalculator->calculatePermissions($account);
    $allowed_ids = $allowed_any_by_status_ids = $allowed_own_by_status_ids = $member_group_ids = [];
    $check_published = $operation === 'view';

    $query = $this->database->select('groups_field_data', 'gfd');
    $query->fields('gfd', ['id', 'type', 'living_space_privacy']);
    $query->distinct();
    $query->condition('gfd.living_space_privacy', NULL, 'IS NOT NULL');
    $results = $query->execute()->fetchAll();

    foreach ($calculated_permissions->getItems() as $item) {
      $identifier = $item->getIdentifier();
      $scope = $item->getScope();

      if ($scope === CGPII::SCOPE_GROUP_TYPE && $results) {
        foreach ($results as $result) {
          if ($result->type == $identifier) {
            $living_space_privacy = $result->living_space_privacy;
            if (!$check_published && $item->hasPermission("{$operation} {$living_space_privacy} group")) {
              $allowed_ids['group'][] = $result->id;
            }
            if ($check_published && $item->hasPermission("{$operation} {$living_space_privacy} group")) {
              $allowed_any_by_status_ids['group'][1][] = $result->id;
            }
            $conditions->addCacheTags(["group:{$result->id}"]);
          }
        }
      }

      // Gather all of the groups the user is a member of.
      if ($scope === CGPII::SCOPE_GROUP) {
        $member_group_ids[] = $identifier;
      }

      if ($item->hasPermission('administer group')) {
        $allowed_ids[$scope][] = $identifier;
      }
      elseif (!$check_published) {
        if ($item->hasPermission($permission)) {
          $allowed_ids[$scope][] = $identifier;
        }
      }
      else {
        if ($item->hasPermission($permission)) {
          $allowed_any_by_status_ids[$scope][1][] = $identifier;
        }
        if ($item->hasPermission('view any unpublished group')) {
          $allowed_any_by_status_ids[$scope][0][] = $identifier;
        }
        if ($item->hasPermission('view own unpublished group')) {
          $allowed_own_by_status_ids[$scope][0][] = $identifier;
        }
      }
    }

    // If no group type or group gave access, we deny access altogether.
    if (empty($allowed_ids) && empty($allowed_any_by_status_ids) && empty($allowed_own_by_status_ids)) {
      $conditions->alwaysFalse();
      return $conditions;
    }

    // We might see multiple values in the $member_group_ids variable because we
    // looped over all calculated permissions multiple times.
    if (!empty($member_group_ids)) {
      $member_group_ids = array_unique($member_group_ids);
    }

    // Add the allowed group types to the query (if any).
    if (!empty($allowed_ids[CGPII::SCOPE_GROUP_TYPE])) {
      $status_conditions = new ConditionGroup();
      $status_conditions->addCondition('type', array_unique($allowed_ids[CGPII::SCOPE_GROUP_TYPE]));

      // If the user had memberships, we need to make sure they are excluded
      // from group type based matches as the memberships' permissions take
      // precedence.
      if (!empty($member_group_ids)) {
        $status_conditions->addCondition('id', $member_group_ids, 'NOT IN');
      }

      $conditions->addCondition($status_conditions);
    }

    // Add the memberships with access to the query (if any).
    if (!empty($allowed_ids[CGPII::SCOPE_GROUP])) {
      $conditions->addCondition('id', array_unique($allowed_ids[CGPII::SCOPE_GROUP]));
    }

    if ($check_published) {
      foreach ([0, 1] as $status) {
        // Nothing gave (un)published access so bail out entirely.
        if (empty($allowed_any_by_status_ids[CGPII::SCOPE_GROUP_TYPE][$status])
          && empty($allowed_any_by_status_ids[CGPII::SCOPE_GROUP][$status])
          && empty($allowed_own_by_status_ids[CGPII::SCOPE_GROUP_TYPE][$status])
          && empty($allowed_own_by_status_ids[CGPII::SCOPE_GROUP][$status])
        ) {
          continue;
        }

        $status_conditions = new ConditionGroup();
        $status_conditions->addCondition('status', $status);
        $status_conditions_inner = new ConditionGroup('OR');

        // Add the allowed group types to the query (if any).
        if (!empty($allowed_any_by_status_ids[CGPII::SCOPE_GROUP_TYPE][$status])) {
          $sub_condition = new ConditionGroup();
          $sub_condition->addCondition('type', array_unique($allowed_any_by_status_ids[CGPII::SCOPE_GROUP_TYPE][$status]), 'IN');

          // If the user had memberships, we need to make sure they are excluded
          // from group type based matches as the memberships' permissions take
          // precedence.
          if (!empty($member_group_ids)) {
            $sub_condition->addCondition('id', $member_group_ids, 'NOT IN');
          }

          $status_conditions_inner->addCondition($sub_condition);
        }

        // Add the memberships with access to the query (if any).
        if (!empty($allowed_any_by_status_ids[CGPII::SCOPE_GROUP][$status])) {
          $status_conditions_inner->addCondition('id', array_unique($allowed_any_by_status_ids[CGPII::SCOPE_GROUP][$status]), 'IN');
        }

        // Nothing gave owner access so try the next publication status.
        if (empty($allowed_own_by_status_ids[CGPII::SCOPE_GROUP_TYPE][$status]) && empty($allowed_own_by_status_ids[CGPII::SCOPE_GROUP][$status])) {
          $status_conditions->addCondition($status_conditions_inner);
          $conditions->addCondition($status_conditions);
          continue;
        }
        $conditions->addCacheContexts(['user']);

        $status_owner_conditions = new ConditionGroup();
        $status_owner_conditions->addCondition('uid', $account->id());
        $status_owner_conditions_inner = new ConditionGroup('OR');

        // Add the allowed owner group types to the query (if any).
        if (!empty($allowed_own_by_status_ids[CGPII::SCOPE_GROUP_TYPE][$status])) {
          $sub_condition = new ConditionGroup();
          $sub_condition->addCondition('type', array_unique($allowed_own_by_status_ids[CGPII::SCOPE_GROUP_TYPE][$status]), 'IN');

          // If the user had memberships, we need to make sure they are excluded
          // from group type based matches as the memberships' permissions take
          // precedence.
          if (!empty($member_group_ids)) {
            $sub_condition->addCondition('id', $member_group_ids, 'NOT IN');
          }

          $status_owner_conditions_inner->addCondition($sub_condition);
        }

        // Add the owner memberships with access to the query (if any).
        if (!empty($allowed_own_by_status_ids[CGPII::SCOPE_GROUP][$status])) {
          $status_owner_conditions_inner->addCondition('id', array_unique($allowed_own_by_status_ids[CGPII::SCOPE_GROUP][$status]), 'IN');
        }

        $status_owner_conditions->addCondition($status_owner_conditions_inner);
        $status_conditions_inner->addCondition($status_owner_conditions);
        $status_conditions->addCondition($status_conditions_inner);
        $conditions->addCondition($status_conditions);
      }
    }

    return $conditions;
  }

}
