<?php

namespace Drupal\living_spaces\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Controller\TitleResolverInterface;

/**
 * Provides a block to display the page title.
 *
 * @Block(
 *   id = "living_spaces_page_title_block",
 *   admin_label = @Translation("Page Title"),
 *   category = @Translation("Living Spaces"),
 * )
 */
class LivingSpacesPageTitleBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the request_stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Returns the current_route_match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $route;

  /**
   * Returns the title_resolver service.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * Returns the path_alias.manager service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Returns the path.matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * Constructs a LivingSpacesPageTitleBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Request stack that controls the lifecycle of requests.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   Provides an interface for classes representing the result of routing.
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   Defines a class which knows how to generate the title from a given route.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   Find an alias for a path and vice versa.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   Provides an interface for URL path matchers.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request, RouteMatchInterface $route, TitleResolverInterface $title_resolver, AliasManagerInterface $alias_manager, PathMatcherInterface $path_matcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->request = $request;
    $this->route = $route;
    $this->titleResolver = $title_resolver;
    $this->aliasManager = $alias_manager;
    $this->pathMatcher = $path_matcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack'),
      $container->get('current_route_match'),
      $container->get('title_resolver'),
      $container->get('path_alias.manager'),
      $container->get('path.matcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'lead' => [],
      'include_hr' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['#tree'] = TRUE;
    $form['lead_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Lead items'),
      '#prefix' => '<div id="lead-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    if (!$form_state->has('lead_count')) {
      $count = !empty($config['lead']) && is_array($config['lead']) ? count($config['lead']) : 1;
      $form_state->set('lead_count', $count);
    }
    $lead_count = $form_state->get('lead_count');

    for ($i = 0; $i < $lead_count; $i++) {
      $form['lead_fieldset']['lead_items'][$i]['lead_text'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Lead text'),
        '#default_value' => isset($config['lead'][$i]['lead_text']) ? $config['lead'][$i]['lead_text'] : '',
      ];
      $form['lead_fieldset']['lead_items'][$i]['lead_path'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Lead path'),
        '#default_value' => isset($config['lead'][$i]['lead_path']) ? $config['lead'][$i]['lead_path'] : '',
        '#suffix' => '<br />',
      ];
    }

    $form['lead_fieldset']['actions'] = [
      '#type' => 'actions',
    ];

    $form['lead_fieldset']['actions']['add_item'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add more'),
      '#submit' => [[$this, 'addOne']],
      '#ajax' => [
        'callback' => [$this, 'addmoreCallback'],
        'wrapper' => 'lead-fieldset-wrapper',
      ],
    ];

    $form['include_hr'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include hr'),
      '#default_value' => $this->configuration['include_hr'],
    ];

    return $form;
  }

  /**
   * Submit handler for the 'Add more' button.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $lead_count = $form_state->get('lead_count');
    $form_state->set('lead_count', $lead_count + 1);
    $form_state->setRebuild();
  }

  /**
   * Ajax handler for the 'Add more' button.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['settings']['lead_fieldset'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $lead_fieldset = $form_state->getValue('lead_fieldset');
    $items = isset($lead_fieldset['lead_items']) ? $lead_fieldset['lead_items'] : [];

    $this->configuration['lead'] = $items;
    $this->configuration['include_hr'] = $form_state->getValue('include_hr');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lead = '';
    if (!empty($this->configuration['lead'])) {
      $url = Url::fromRoute('<current>')->toString();
      $path = $this->aliasManager->getAliasByPath($url);

      foreach ($this->configuration['lead'] as $item) {
        if ($this->pathMatcher->matchPath($path, $item['lead_path'])) {
          // phpcs:ignore
          $lead = $this->t($item['lead_text']);
        }
      }
    }

    $tags = [];
    foreach ($this->route->getParameters() as $entity) {
      if ($entity instanceof EntityInterface) {
        $tags = Cache::mergeTags($tags, $entity->getCacheTags());
      }
    }

    return [
      '#type' => 'page_title',
      '#title' => $this->titleResolver->getTitle($this->request->getCurrentRequest(), $this->route->getRouteObject()),
      '#lead' => $lead,
      '#include_hr' => $this->configuration['include_hr'],
      '#cache' => ['contexts' => ['url'], 'tags' => $tags],
    ];
  }

}
