<?php

namespace Drupal\living_spaces_group\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Provides a space members block.
 *
 * @Block(
 *   id = "living_spaces_group_members",
 *   admin_label = @Translation("Space members"),
 *   category = @Translation("Living Spaces"),
 *   context_definitions = {
 *     "group" = @ContextDefinition("entity:group", label = @Translation("Space"))
 *   }
 * )
 */
class LivingSpacesGroupMembersBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Current user proxy.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Block manager service.
   *
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->currentUser = $container->get('current_user');
    $instance->moduleHandler = $container->get('module_handler');
    $instance->blockManager = $container->get('plugin.manager.block');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'enabled' => [],
      'enabled_contacts' => [],
      'enabled_exports' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $tabs = $this->getTabOptions();
    $form['enabled'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enabled tabs'),
      '#default_value' => $this->configuration['enabled'],
      '#options' => $tabs,
    ];

    foreach ($tabs as $tab => $label) {
      $form["{$tab}_label"] = [
        '#type' => 'textfield',
        '#title' => $this->t('@tab tab label', ['@tab' => $label]),
        '#default_value' => $this->configuration["{$tab}_label"],
        '#states' => [
          'visible' => [
            ":input[name='settings[enabled][{$tab}]']" => ['checked' => TRUE],
          ],
          'required' => [
            ":input[name='settings[enabled][{$tab}]']" => ['checked' => TRUE],
          ],
        ],
      ];
    }

    $form['members_view_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Members view label'),
      '#default_value' => $this->configuration['members_view_label'],
      '#states' => [
        'visible' => [
          ":input[name='settings[enabled][members]']" => ['checked' => TRUE],
        ],
        'required' => [
          ":input[name='settings[enabled][members]']" => ['checked' => TRUE],
        ],
      ],
    ];

    $form['admins_view_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Administrators view label'),
      '#default_value' => $this->configuration['admins_view_label'],
      '#states' => [
        'visible' => [
          ":input[name='settings[enabled][admins]']" => ['checked' => TRUE],
        ],
        'required' => [
          ":input[name='settings[enabled][admins]']" => ['checked' => TRUE],
        ],
      ],
    ];

    $form['enabled_contacts'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enabled contacts'),
      '#default_value' => $this->configuration['enabled_contacts'],
      '#options' => $this->moduleHandler->invokeAll('living_spaces_group_contact_info'),
    ];

    $form['enabled_exports'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enabled exports'),
      '#default_value' => $this->configuration['enabled_exports'],
      '#options' => $this->moduleHandler->invokeAll('living_spaces_group_exports_info'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['enabled'] = $form_state->getValue('enabled');
    foreach ($this->getTabOptions() as $tab => $name) {
      if ($value = $form_state->getValue("{$tab}_label")) {
        $this->configuration["{$tab}_label"] = $value;
      }
    }

    $this->configuration['members_view_label'] = $form_state->getValue('members_view_label');
    $this->configuration['admins_view_label'] = $form_state->getValue('admins_view_label');
    $this->configuration['enabled_contacts'] = $form_state->getValue('enabled_contacts');
    $this->configuration['enabled_exports'] = $form_state->getValue('enabled_exports');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $tabs = $this->getTabOptions();
    $build = [
      '#theme' => 'horizontal_tabs',
      '#instance_id' => 'members-tabs',
      '#items' => [],
    ];
    foreach ($this->configuration['enabled'] as $tab) {
      if ($tab) {
        $build['#items'][] = [
          'id' => $tab,
          'header' => $this->configuration["{$tab}_label"] ?? $tabs[$tab],
          'body' => $this->getTabContent($tab),
        ];
      }
    }

    return !empty($build['#items']) ? $build : [];
  }

  /**
   * Returns available tab options.
   *
   * @return array
   *   Array of keys and translated labels for tabs.
   */
  protected function getTabOptions() {
    $options = [
      'members' => $this->t('Members'),
      'admins' => $this->t('Administrators'),
    ];
    if (($this->currentUser->hasPermission('manage circle spaces') &&
      $this->moduleHandler->moduleExists('living_spaces_circles')) ||
      $this->moduleHandler->moduleExists('living_spaces_subgroup')
    ) {
      $options['inherit'] = $this->t('Inherited');
    }
    return $options;
  }

  /**
   * Returns tab render array based on argument.
   *
   * @param string $tab
   *   Tab machine name.
   *
   * @return array
   *   Content for the tab.
   */
  protected function getTabContent(string $tab) {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $this->getContextValue('group');

    $build = [];
    switch ($tab) {
      case 'members':
        $build['members'] = $this->getMembersViewRender($group->id());
        $build['contact'] = $this->getContactActionsRender($group);
        $build['export'] = $this->getExportActionsViewRender($group);
        break;

      case 'admins':
        $build['members'] = $this->getMembersViewRender($group->id(), TRUE);
        break;

      case 'inherit':
        if ($this->moduleHandler->moduleExists('living_spaces_subgroup')) {
          $build['tree'] = $this->getGroupTreeRender($group);
        }

        /** @var \Drupal\group\Entity\Storage\GroupRoleStorageInterface $role_storage */
        $role_storage = $this->entityTypeManager->getStorage('group_role');
        $roles = $role_storage->loadByUserAndGroup($this->currentUser, $group);
        $current_is_admin = FALSE;
        foreach ($roles as $role) {
          if ($role->get('is_space_admin')) {
            $current_is_admin = TRUE;
            break;
          }
        }

        if ($this->moduleHandler->moduleExists('living_spaces_circles') &&
          (
            $current_is_admin ||
            $this->currentUser->hasPermission('manage circle spaces') ||
            $group->hasPermission('manage circle spaces', $this->currentUser)
          )
        ) {
          $build['circle'] = $this->getCircleFormRender($group);

          $url = Url::fromRoute('entity.group.add_form', [
            'group_type' => 'circle',
          ], [
            'query' => ['space' => $group->id()],
          ]);

          $build['add_cricle'] = [
            '#type' => 'link',
            '#url' => $url,
            '#title' => $this->t('Add circle'),
            '#attributes' => ['class' => ['btn', 'btn-primary']],
          ];
        }
        break;

    }

    return $build;
  }

  /**
   * Returns render array for members or admins tab.
   *
   * @param string $group_id
   *   Group entity ID.
   * @param bool $is_admin
   *   Whether to include only admins.
   *
   * @return array
   *   Render array.
   */
  protected function getMembersViewRender($group_id, $is_admin = FALSE) {
    $view = Views::getView('members');
    if ($is_admin) {
      $view->setExposedInput([
        'is_space_admin' => '1',
      ]);
    }
    $view->attachment_before[] = [
      '#type' => 'html_tag',
      '#tag' => 'h3',
      '#value' => $is_admin
        ? $this->configuration['admins_view_label']
        : $this->configuration['members_view_label'],
    ];
    return [
      '#type' => 'view',
      '#view' => $view,
      '#name' => 'members',
      '#display_id' => 'members_by_role',
      '#arguments' => [$group_id],
    ];
  }

  /**
   * Returns render array for contact actions on a space group.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Space group entity.
   *
   * @return array
   *   Render array.
   */
  protected function getContactActionsRender(GroupInterface $group) {
    $output = [
      '#type' => 'container',
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h4',
        '#value' => t('Contact'),
        '#attributes' => ['class' => ['living-space-contact-action-title']],
      ],
    ];

    $hook = 'living_spaces_group_contact_info';
    $account = $this->currentUser->getAccount();
    $actions = $this->moduleHandler->invokeAll($hook, [FALSE, $group, $account]);
    if (!empty($actions)) {
      $output += array_intersect_key($actions, array_filter($this->configuration['enabled_contacts']));
    }
    else {
      $output = [];
    }

    return $output;
  }

  /**
   * Returns render array for export actions on a space group.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Space group entity.
   *
   * @return array
   *   Render array.
   */
  protected function getExportActionsViewRender(GroupInterface $group) {
    $output = [
      '#type' => 'container',
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h4',
        '#value' => t('Export'),
        '#attributes' => ['class' => ['living-space-export-action-title']],
      ],
    ];

    $actions = $this->moduleHandler
      ->invokeAll('living_spaces_group_exports_info', [FALSE, $group]);
    if (!empty($actions)) {
      $output += array_intersect_key($actions, array_filter($this->configuration['enabled_exports']));
    }
    else {
      $output = [];
    }
    return $output;
  }

  /**
   * Returns render array for group tree.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Space group entity.
   *
   * @return array
   *   Render array.
   */
  protected function getGroupTreeRender(GroupInterface $group) {
    $plugin_block = $this->blockManager->createInstance('living_spaces_group_tree_block', [
      'id' => 'living_spaces_group_tree_block',
      'context' => [
        'group' => $group,
      ],
    ]);

    return $plugin_block->build();
  }

  /**
   * Returns render array for circle form.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Space group entity.
   *
   * @return array
   *   Render array.
   */
  protected function getCircleFormRender(GroupInterface $group) {
    $plugin_block = $this->blockManager->createInstance('living_spaces_circles_add_people_block', [
      'id' => 'living_spaces_circles_add_people_block',
      'context' => [
        'group' => $group,
      ],
    ]);

    return $plugin_block->build();
  }

}
