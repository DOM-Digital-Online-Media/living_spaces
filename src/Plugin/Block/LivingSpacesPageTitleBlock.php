<?php

namespace Drupal\living_spaces\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request, RouteMatchInterface $route, TitleResolverInterface $title_resolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->request = $request;
    $this->route = $route;
    $this->titleResolver = $title_resolver;
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
      $container->get('title_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'lead' => '',
      'include_hr' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['lead'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Lead'),
      '#default_value' => $this->configuration['lead'],
    ];

    $form['include_hr'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include hr'),
      '#default_value' => $this->configuration['include_hr'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['lead'] = $form_state->getValue('lead');
    $this->configuration['include_hr'] = $form_state->getValue('include_hr');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'page_title',
      '#title' => $this->titleResolver->getTitle($this->request->getCurrentRequest(), $this->route->getRouteObject()),
      '#lead' => $this->configuration['lead'],
      '#include_hr' => $this->configuration['include_hr'],
    ];
  }

}
