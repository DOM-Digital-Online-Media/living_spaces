<?php

namespace Drupal\living_spaces_subgroup\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\living_spaces_subgroup\LivingSpacesSubgroupManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ResultRow;
use Drupal\Core\Cache\Cache;

/**
 * Field handler to group parent.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("living_spaces_subgroup_parent_field")
 */
class LivingSpacesSubgroupParentField extends FieldPluginBase {

  /**
   * Returns the living_spaces_subgroup.manager service.
   *
   * @var \Drupal\living_spaces_subgroup\LivingSpacesSubgroupManagerInterface
   */
  protected $livingSubgroupManager;

  /**
   * Constructs an LivingGroupParent object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\living_spaces_subgroup\LivingSpacesSubgroupManagerInterface $living_subgroup_manager
   *   Manager service to operate and get info on sub-group relations.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LivingSpacesSubgroupManagerInterface $living_subgroup_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->livingSubgroupManager = $living_subgroup_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('living_spaces_subgroup.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['all'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['all'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('All parents'),
      '#default_value' => $this->options['all'],
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $output = '';

    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $values->_entity;
    $tags = $group->getCacheTags();
    $contexts = $group->getCacheContexts();

    if ($parents = $this->livingSubgroupManager->getGroupsParents($group, FALSE)) {
      $parents = array_values($parents);

      $output .= $parents[0]->toLink($parents[0]->label())->toString();
      $tags = Cache::mergeTags($tags, $parents[0]->getCacheTags());
      $contexts = Cache::mergeContexts($contexts, $parents[0]->getCacheContexts());

      if ($this->options['all']) {
        array_shift($parents);

        foreach ($parents as $parent) {
          $output .= '<br />/ ' . $parent->toLink($parent->label())->toString();
          $tags = Cache::mergeTags($tags, $parent->getCacheTags());
          $contexts = Cache::mergeContexts($contexts, $parent->getCacheContexts());
        }
      }

      $output .= '<br /> / ' . $group->toLink($group->label())->toString();
    }

    return [
      '#markup' => $output,
      '#cache' => [
        'contexts' => $contexts,
        'tags' => $tags,
      ],
    ];
  }

}
