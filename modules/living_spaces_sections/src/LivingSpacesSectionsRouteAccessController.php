<?php

namespace Drupal\living_spaces_sections;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\group\Entity\GroupInterface;

/**
 * Provides access controller for custom access route checking.
 */
class LivingSpacesSectionsRouteAccessController {
  use StringTranslationTrait;

  /**
   * Route access method for viewing a section on particular group page.
   *
   * @param string $group
   *   Group id.
   * @param string $section
   *   Section type that's attached directly to group entity.
   * @param string|null $sub_section
   *   Section type that's attached to parent section entity. May not exist.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   View access.
   */
  public static function checkViewAccess($group, $section, $sub_section = NULL) {
    /** @var \Drupal\group\Entity\GroupInterface $group_entity */
    $group_entity = \Drupal::entityTypeManager()
      ->getStorage('group')
      ->load($group);
    /** @var \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface $service */
    $service = \Drupal::service('living_spaces_sections.manager');

    if (!$group_entity ||
      !$service->isSectionsEnabled($group_entity->bundle()) ||
      !$group_entity->access('view')
    ) {
      return AccessResult::forbidden();
    }

    $path = implode('/', [$section, $sub_section]);
    $section = $service->getSectionFromGroupByPath($group_entity, $path);
    return AccessResult::allowedIf($section && $section->access('view'));
  }

  /**
   * Returns whether the user has manage sections access in group context.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Living space group entity.
   * @param \Drupal\Core\Session\AccountInterface|null $user
   *   User which wants to manage sections. Defaults to current user.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Allowed if user has access to managing the group's sections.
   */
  public static function checkManageAccess(GroupInterface $group, AccountInterface $user = NULL) {
    /** @var \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface $service */
    $service = \Drupal::service('living_spaces_sections.manager');

    if (!$user) {
      $user = \Drupal::currentUser();
    }

    // Check if sections enabled for the group type.
    if (!$service->isSectionsEnabled($group->bundle())) {
      return AccessResult::forbidden();
    }

    // Check if user has global access or certain group access.
    if ($user->hasPermission('administer living spaces sections settings') ||
      $user->hasPermission('manage living spaces sections settings') ||
      $group->hasPermission('administer living spaces sections settings', $user) ||
      $group->hasPermission('manage living spaces sections settings', $user)
    ) {
      return AccessResult::allowed();
    }

    return AccessResult::neutral();
  }

}
