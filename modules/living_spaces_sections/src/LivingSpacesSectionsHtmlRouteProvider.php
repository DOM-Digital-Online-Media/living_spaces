<?php

namespace Drupal\living_spaces_sections;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for living space section entities.
 */
class LivingSpacesSectionsHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * Section route.
   *
   * @var string
   */
  const SECTION_ROUTE = 'living_spaces_sections.section_view';

  /**
   * Sub-section route.
   *
   * @var string
   */
  const SUB_SECTION_ROUTE = 'living_spaces_sections.sub_section_view';

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    // Add a route for enabled.
    $collection->add(self::SECTION_ROUTE, $this->getLivingSpacesSectionRoute($entity_type));
    $collection->add(self::SUB_SECTION_ROUTE, $this->getLivingSpacesSubSectionRoute($entity_type));

    return $collection;
  }

  /**
   * Returns route for enabled sections in a living space.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   Entity type object.
   *
   * @return \Symfony\Component\Routing\Route
   *   Route for enabled sub-sections.
   */
  public function getLivingSpacesSectionRoute(EntityTypeInterface $entity_type) {
    $route = new Route("group/{group}/{section}");
    $route->addDefaults([
      '_entity_view' => "{$entity_type->id()}.full",
      '_title_callback' => '\Drupal\Core\Entity\Controller\EntityController::title',
    ])
      ->setRequirements([
        'group' => '\d+',
        'section' => '[a-z0-9-]+',
        '_custom_access' => '\Drupal\living_spaces_sections\LivingSpacesSectionsRouteAccessController::checkViewAccess',
      ])
      ->setOption('parameters', [
        'group' => ['type' => 'entity:group'],
      ]);
    return $route;
  }

  /**
   * Returns route for enabled sub-sections in a living space.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   Entity type object.
   *
   * @return \Symfony\Component\Routing\Route
   *   Route for enabled sub-sections.
   */
  public function getLivingSpacesSubSectionRoute(EntityTypeInterface $entity_type) {
    $route = new Route("group/{group}/{section}/{sub_section}");
    $route->addDefaults([
      '_entity_view' => "{$entity_type->id()}.full",
      '_title_callback' => '\Drupal\Core\Entity\Controller\EntityController::title',
    ])
      ->setRequirements([
        'group' => '\d+',
        'section' => '[a-z0-9-]+',
        'sub_section' => '[a-z0-9-]+',
        '_custom_access' => '\Drupal\living_spaces_sections\LivingSpacesSectionsRouteAccessController::checkViewAccess',
      ])
      ->setOption('parameters', [
        'group' => ['type' => 'entity:group'],
      ]);
    return $route;
  }

}
