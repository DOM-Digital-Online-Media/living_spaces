<?php

namespace Drupal\living_spaces_sections\Context;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface;
use Drupal\living_spaces_sections\LivingSpacesSectionsHtmlRouteProvider;
use Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface;

/**
 * Provides group context from section-related routes and layout builder.
 */
class LivingSpacesSectionsGroupContext implements ContextProviderInterface {
  use StringTranslationTrait;

  /**
   * Section manager service.
   *
   * @var \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface
   */
  protected $sectionManager;

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * LivingSpacesSectionsGroupContext constructor.
   */
  public function __construct(LivingSpacesSectionsManagerInterface $sections_manager, TranslationInterface $translation, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    $this->sectionManager = $sections_manager;
    $this->setStringTranslation($translation);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {
    $value = NULL;
    $create_dummy = FALSE;

    switch ($this->routeMatch->getRouteName()) {
      // Get group context for default section view.
      case 'layout_builder.defaults.living_spaces_section.view':
        $create_dummy = TRUE;
        break;

      // Get group context for section layout builder.
      case 'layout_builder.choose_block':
      case 'layout_builder.add_block':
      case 'layout_builder.overrides.living_spaces_section.view':
        /** @var \Drupal\layout_builder\SectionStorageInterface $section_storage */
        $section_storage = $this->routeMatch->getParameter('section_storage');

        try {
          $entity = $section_storage->getContextValue('entity');

          // If we have a living space section, then we may have a group.
          if ($entity instanceof LivingSpacesSectionInterface) {
            $value = $entity->getGroup();
          }
        }
        catch (ContextException $e) {
          // If we don't have entity context, then we check type and
          // provide dummy group context if we are managing section's layout.
          [$entity_type] = explode('.', $section_storage->getStorageId());
          if ($entity_type === 'living_spaces_section') {
            $create_dummy = TRUE;
          }
        }
        break;

      case LivingSpacesSectionsHtmlRouteProvider::SECTION_ROUTE:
      case LivingSpacesSectionsHtmlRouteProvider::SUB_SECTION_ROUTE:
        $value = $this->routeMatch->getParameter('group');
        break;

    }

    // Create a dummy group using random bundle to provide group context for
    // cases when we don't have connection between section and group yet.
    if ($create_dummy) {
      $types = $this->entityTypeManager->getStorage('group_type')
        ->loadMultiple();

      if ($types) {
        $value = $this->entityTypeManager->getStorage('group')
          ->create(['type' => key($types)]);
      }
    }

    $cacheability = new CacheableMetadata();
    $cacheability->setCacheContexts(['route']);

    $context_definition = EntityContextDefinition::create('group')
      ->setRequired(FALSE)
      ->setLabel($this->t('Group from section'));
    $context = new Context($context_definition, $value);
    $context->addCacheableDependency($cacheability);
    return ['group' => $context];
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    return $this->getRuntimeContexts([]);
  }

}
