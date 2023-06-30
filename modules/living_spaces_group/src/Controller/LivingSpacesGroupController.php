<?php

namespace Drupal\living_spaces_group\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\group\Entity\GroupInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Returns responses for Space Group routes.
 */
class LivingSpacesGroupController extends ControllerBase {

  /**
   * Returns the request_stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a LivingSpacesGroupController object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack that controls the lifecycle of requests.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * Removes member from space and redirects to space members page.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Space group entity.
   * @param \Drupal\user\UserInterface $user
   *   User for which action was triggered.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to space members page.
   */
  public function removeMember(GroupInterface $group, UserInterface $user) {
    if ($membership = $group->getMember($user)) {
      $membership->getGroupRelationship()->delete();
    }

    return new RedirectResponse(
      Url::fromRoute('page_manager.page_view_living_space_members_living_space_members-layout_builder-0', [
        'group' => $group->id()
      ])->setAbsolute()->toString()
    );
  }

  /**
   * Block user and redirects to space members page.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Space group entity.
   * @param \Drupal\user\UserInterface $user
   *   User for which action was triggered.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to space members page.
   */
  public function blockUser(GroupInterface $group, UserInterface $user) {
    $user->block();
    $user->save();

    return new RedirectResponse(
      Url::fromRoute('page_manager.page_view_living_space_members_living_space_members-layout_builder-0', [
        'group' => $group->id()
      ])->setAbsolute()->toString()
    );
  }

  /**
   * Unblock user and redirects to space members page.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Space group entity.
   * @param \Drupal\user\UserInterface $user
   *   User for which action was triggered.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to space members page.
   */
  public function unblockUser(GroupInterface $group, UserInterface $user) {
    $user->activate();
    $user->save();

    return new RedirectResponse(
      Url::fromRoute('page_manager.page_view_living_space_members_living_space_members-layout_builder-0', [
        'group' => $group->id()
      ])->setAbsolute()->toString()
    );
  }

  /**
   * Assigns space admin role to the user.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Space group entity.
   * @param \Drupal\user\UserInterface $user
   *   User for which action was triggered.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to space members page.
   */
  public function assignAdmin(GroupInterface $group, UserInterface $user) {
    /** @var \Drupal\group\Entity\Storage\GroupRoleStorageInterface $storage */
    $storage = $this->entityTypeManager()->getStorage('group_role');
    $admin_roles = $storage->loadByProperties([
      'group_type' => $group->getGroupType()->id(),
      'is_space_admin' => TRUE,
    ]);
    $admin_role = $admin_roles ? key($admin_roles) : NULL;
    if ($admin_role) {
      $membership = $group->getMember($user);
      $relationship = $membership->getGroupRelationship();
      $relationship->get('group_roles')->appendItem($admin_role);
      $relationship->save();
    }
    return new RedirectResponse(
      Url::fromRoute('page_manager.page_view_living_space_members_living_space_members-layout_builder-0', [
        'group' => $group->id()
      ])->setAbsolute()->toString()
    );
  }

  /**
   * Removes space admin role from the user.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Space group entity.
   * @param \Drupal\user\UserInterface $user
   *   User for which action was triggered.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to space members page.
   */
  public function removeAdmin(GroupInterface $group, UserInterface $user) {
    /** @var \Drupal\group\Entity\Storage\GroupRoleStorageInterface $storage */
    $storage = $this->entityTypeManager()->getStorage('group_role');
    $admin_roles = $storage->loadByProperties([
      'group_type' => $group->getGroupType()->id(),
      'is_space_admin' => TRUE,
    ]);
    $relationship = $group->getMember($user)->getGroupRelationship();
    $role_ids = $relationship->get('group_roles')->getValue();
    foreach ($role_ids as $key => $id) {
      if (in_array($id['target_id'], array_keys($admin_roles))) {
        unset($role_ids[$key]);
      }
    }
    $relationship->set('group_roles', array_values($role_ids));
    $relationship->save();

    return new RedirectResponse(
      Url::fromRoute('page_manager.page_view_living_space_members_living_space_members-layout_builder-0', [
        'group' => $group->id()
      ])->setAbsolute()->toString()
    );
  }

  /**
   * Returns response for the preferred spaces redirect.
   */
  public function spaceRedirect($type) {
    $account = $this->entityTypeManager()->getStorage('user')
      ->load($this->currentUser()->id());

    if (!$account->get('preferred_spaces')->isEmpty()) {
      $gids = [];
      foreach ($account->get('preferred_spaces')->getValue() as $value) {
        $gids[] = $value['target_id'];
      }

      $properties = ['id' => $gids, 'type' => $type];
      if ($groups = $this->entityTypeManager()->getStorage('group')->loadByProperties($properties)) {
        $group = reset($groups);
        return $this->redirect('entity.group.canonical', ['group' => $group->id()], ['absolute' => TRUE]);
      }
    }

    $this->messenger()->addWarning($this->t('Add a group of <b>@type</b> type to the "Preferred spaces" field.', ['@type' => $type]));
    return $this->redirect('entity.user.edit_form', ['user' => $this->currentUser()->id()], ['absolute' => TRUE]);
  }

  /**
   * Returns response for the join space route.
   */
  public function join(GroupInterface $group) {
    /** @var \Drupal\user\UserInterface $user */
    $user = $this->entityTypeManager->getStorage('user')
      ->load($this->currentUser()->id());
    $group->addMember($user);

    $this->messenger()->addStatus($this->t('You have joined the group.'));
    $query = $this->requestStack->getCurrentRequest()->query;

    if ($query->has('destination')) {
      return new RedirectResponse($query->get('destination'));
    }

    return new RedirectResponse($group->toUrl()->toString());
  }

}
