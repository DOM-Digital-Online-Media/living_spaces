<?php

namespace Drupal\living_spaces_group_privacy\Plugin;

use Drupal\Core\Access\AccessResult;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\group\GroupMembershipLoaderInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\node\NodeInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines a base living_spaces_group_privacy implementation that most plugins will extend.
 */
class LivingSpacesGroupPrivacyBase extends PluginBase implements LivingSpacesGroupPrivacyInterface {
  use StringTranslationTrait;

  /**
   * Group membership loader service.
   *
   * @var \Drupal\group\GroupMembershipLoaderInterface
   */
  protected $membershipLoader;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LivingSpacesGroupPrivacyManagerInterface $channel_manager, Connection $database, EntityTypeManagerInterface $entity_type_manager, CacheTagsInvalidatorInterface $invalidator, TranslationInterface $translation, ModuleHandlerInterface $module_handler, GroupMembershipLoaderInterface $membership_loader) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration = $configuration;
    $this->channelManager = $channel_manager;
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->invalidator = $invalidator;
    $this->moduleHandler = $module_handler;
    $this->membershipLoader = $membership_loader;
    $this->setStringTranslation($translation);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.living_spaces_group_privacy'),
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('cache_tags.invalidator'),
      $container->get('string_translation'),
      $container->get('module_handler'),
      $container->get('group.membership_loader')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->getPluginId();
  }

  /**
   * Returns plugin label.
   */
  public function label() {
    return $this->getPluginDefinition()['label']->__toString();
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupNodeGrants(NodeInterface $node, GroupInterface $group) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupUserGrants(AccountInterface $account, $op) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function joinAccess(GroupInterface $group, AccountInterface $account) {
    return $group->hasPermission('join ' . $this->getPluginId() . ' group', $account);
  }

  /**
   * {@inheritdoc}
   */
  public function groupAccess(GroupInterface $group, $operation, AccountInterface $account) {
    $access = $account->hasPermission('bypass group access');

    if (!$access) {
      $access = $group->hasPermission('administer group', $account);
    }
    if (!$access) {
      if ('update' == $operation) {
        $access = $group->hasPermission('edit group', $account);
      }
      else {
        $access = $group->hasPermission("{$operation} group", $account);
      }
    }
    if (!$access) {
      $access = $group->hasPermission("{$operation} {$this->getPluginId()} group", $account);
    }

    return $access ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
