<?php

namespace Drupal\living_spaces_group_privacy;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions for living_spaces_group_privacy module.
 */
class LivingSpacesGroupPrivacyPermissions implements ContainerInjectionInterface {
  use StringTranslationTrait;

  /**
   * Space privacy manager service.
   *
   * @var \Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyManagerInterface
   */
  protected $privacyManager;

  /**
   * Constructs a new LivingSpacesGroupPrivacyPermissions instance.
   *
   * @param \Drupal\living_spaces_group_privacy\Plugin\LivingSpacesGroupPrivacyManagerInterface $privacy_manager
   *   Describes basic method for living_spaces_group_privacy manager.
   */
  public function __construct(LivingSpacesGroupPrivacyManagerInterface $privacy_manager) {
    $this->privacyManager = $privacy_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.living_spaces_group_privacy'));
  }

  /**
   * Returns dynamic permissions per privacy type.
   */
  public function privacyTypePermissions() {
    $permissions = [];

    foreach ($this->privacyManager->getPrivacyPlugins() as $instance) {
      $permissions["view {$instance->id()} group"] = [
        'title' => $this->t('View @name group', [
          '@name' => $instance->label(),
        ])->__toString(),
      ];
      $permissions["update {$instance->id()} group"] = [
        'title' => $this->t('Update @name group', [
          '@name' => $instance->label(),
        ])->__toString(),
      ];
      $permissions["delete {$instance->id()} group"] = [
        'title' => $this->t('Delete @name group', [
          '@name' => $instance->label(),
        ])->__toString(),
      ];
      $permissions["join {$instance->id()} group"] = [
        'title' => $this->t('Join @name group', [
          '@name' => $instance->label(),
        ])->__toString(),
      ];
    }

    return $permissions;
  }

}
