<?php

namespace Drupal\living_spaces_group\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\group\Plugin\GroupContentEnablerManagerInterface;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Access\AccessResult;

/**
 * LivingSpacesGroupCreateContentController class.
 */
class LivingSpacesGroupCreateContentController extends ControllerBase {

  /**
   * Returns the plugin.manager.group_content_enabler service.
   *
   * @var \Drupal\group\Plugin\GroupContentEnablerManagerInterface
   */
  protected $groupContentEnabler;

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new LivingSpacesGroupCreateContentController object.
   *
   * @param \Drupal\group\Plugin\GroupContentEnablerManagerInterface $group_content_enabler
   *   Provides a common interface for group content enabler managers.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(GroupContentEnablerManagerInterface $group_content_enabler, EntityTypeManagerInterface $entity_type_manager) {
    $this->groupContentEnabler = $group_content_enabler;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.group_content_enabler'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Shows plugin content links.
   */
  public function createContent(GroupInterface $group, $plugin) {
    $plugin_ids = [];
    foreach ($this->groupContentEnabler->getInstalledIds($group->getGroupType()) as $content_plugin) {
      if (strpos($content_plugin, "{$plugin}:") !== FALSE) {
        $plugin_ids[] = $content_plugin;
      }
    }

    if (empty($plugin_ids)) {
      throw new AccessDeniedHttpException();
    }

    if (count($plugin_ids) == 1) {
      return $this->redirect('entity.group_content.create_form', [
        'group' => $group->id(),
        'plugin_id' => $plugin_ids[0],
      ]);
    }

    $build = ['#theme' => 'entity_add_list', '#bundles' => []];
    foreach ($plugin_ids as $plugin_id) {
      $content_plugin = $group->getGroupType()->getContentPlugin($plugin_id);
      $label = $content_plugin->getLabel();

      $build['#bundles'][$plugin_id] = [
        'label' => $label,
        'description' => $content_plugin->getContentTypeDescription(),
        'add_link' => Link::createFromRoute($label, 'entity.group_content.create_form', [
          'group' => $group->id(),
          'plugin_id' => $plugin_id,
        ]),
      ];
    }

    $bundle_entity_type = $this->entityTypeManager->getDefinition('group_content_type');
    $build['#cache']['tags'] = $bundle_entity_type->getListCacheTags();

    return $build;
  }

  /**
   * Access callback for 'plugin content links' route.
   */
  public function access(GroupInterface $group, $plugin) {
    $access = $this->currentUser()->hasPermission('manage living spaces');

    if (!$access) {
      $access = $group->hasPermission('administer group', $this->currentUser());
    }

    return $access ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
