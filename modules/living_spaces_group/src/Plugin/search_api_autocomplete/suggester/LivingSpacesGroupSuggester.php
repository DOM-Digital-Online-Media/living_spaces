<?php

namespace Drupal\living_spaces_group\Plugin\search_api_autocomplete\suggester;

use Drupal\search_api_autocomplete\Plugin\search_api_autocomplete\suggester\Server;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\SearchApiException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a suggester plugin that retrieves suggestions from the server.
 *
 * @SearchApiAutocompleteSuggester(
 *   id = "living_spaces_group_server",
 *   label = @Translation("Living Spaces: Retrieve from server"),
 *   description = @Translation("Make suggestions based on the data indexed on the server."),
 * )
 */
class LivingSpacesGroupSuggester extends Server {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|null
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $plugin = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $plugin->setRequesStack($container->get('request_stack'));

    return $plugin;
  }

  /**
   * Retrieves the request stack.
   *
   * @return \Symfony\Component\HttpFoundation\RequestStack
   *   The request stack
   */
  public function getRequesStack() {
    return $this->requestStack ?: \Drupal::service('request_stack');
  }

  /**
   * Sets the request stack.
   *
   * @return $this
   */
  public function setRequesStack(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAutocompleteSuggestions(QueryInterface $query, $incomplete_key, $user_input) {
    $index = $query->getIndex();

    if (!($backend = static::getBackend($index))) {
      return [];
    }

    // If the "Transliteration" processor is enabled for the search index, we
    // also need to transliterate the user input for autocompletion.
    if ($index->isValidProcessor('transliteration')) {
      $langcode = $this->getLanguageManager()->getCurrentLanguage()->getId();
      $incomplete_key = $this->getTransliterator()->transliterate($incomplete_key, $langcode);
      $user_input = $this->getTransliterator()->transliterate($user_input, $langcode);
    }

    $parameters = $this->getRequesStack()->getCurrentRequest()->query->all();
    if ($this->configuration['fields']) {
      $query->setFulltextFields($this->configuration['fields']);
    }
    elseif (!empty($parameters['filter'])) {
      $parameters['filter'] = str_replace('ngram_', '', $parameters['filter']);
      $query->setFulltextFields([$parameters['filter']]);
    }

    try {
      $query->preExecute();
    }
    catch (SearchApiException $e) {
      return [];
    }

    return $backend->getAutocompleteSuggestions($query, $this->getSearch(), $incomplete_key, $user_input);
  }

}
