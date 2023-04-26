<?php

namespace Drupal\living_spaces_group\Plugin\views\filter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter handler to joined spaces of the current user.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("living_spaces_group_user_joined_spaces")
 */
class LivingSpacesGroupInUserJoinedSpaces extends FilterPluginBase {

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
   * Constructs a LivingSpacesUsersJoinedSpaces object.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    if ($form_state->get('exposed')) {
      $form['value'] = [
        '#title' => $this->t('Group is in joined spaces of current user'),
        '#type' => 'select',
        '#default_value' => 'any',
        '#options' => [
          'yes' => $this->t('Yes'),
          'no' => $this->t('No'),
        ],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $this->options['group_in_user_joined_spaces'] = $this->options['group_in_user_joined_spaces'] ?? '';
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['group_in_user_joined_spaces'] = [
      '#title' => $this->t('Group is in joined spaces of current user'),
      '#type' => 'select',
      '#default_value' => $this->value[0],
      '#options' => [
        '' => $this->t('- Any -'),
        'yes' => $this->t('Yes'),
        'no' => $this->t('No'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function valueSubmit($form, FormStateInterface $form_state) {
    $this->options['value'] = [$form_state->getValues()['options']['group_in_user_joined_spaces']];
  }

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    if ($this->isAGroup()) {
      return $this->t('grouped');
    }
    if (!empty($this->options['exposed'])) {
      return $this->t('exposed');
    }
    return $this->value[0];
  }

  /**
   * {@inheritdoc}
   */
  public function query(): void {
    $this->ensureMyTable();

    if (isset($this->value[0])) {
      $operator = $this->value[0] == 'yes' ? 'IN' : 'NOT IN';

      $joined_spaces = [0];
      $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
      foreach ($user->get('joined_spaces')->getValue() as $value) {
        if ($space = $this->entityTypeManager->getStorage('group')->load($value['target_id'])) {
          $joined_spaces[$value['target_id']] = $value['target_id'];
        }
      }

      $this->query->addWhere(
        $this->options['group'],
        $this->tableAlias . '.' . $this->realField,
        $joined_spaces,
        $operator
      );
    }

  }

}
