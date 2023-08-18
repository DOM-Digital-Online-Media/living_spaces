<?php

namespace Drupal\living_spaces_sections\Enhancer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\EnhancerInterface;
use Drupal\living_spaces_sections\LivingSpacesSectionsHtmlRouteProvider;
use Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Enhances routes which use section path and group to fetch section entity.
 */
class LivingSpacesSectionsRouteEnhancer implements EnhancerInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Sections manager service.
   *
   * @var \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface
   */
  protected $sectionsManager;

  /**
   * LivingSpacesSectionsRouteEnhancer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   * @param \Drupal\living_spaces_sections\LivingSpacesSectionsManagerInterface $sectionsManager
   *   Living space sections manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LivingSpacesSectionsManagerInterface $sectionsManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->sectionsManager = $sectionsManager;
  }

  /**
   * {@inheritdoc}
   */
  public function enhance(array $defaults, Request $request) {
    $route_name = $defaults[RouteObjectInterface::ROUTE_NAME];
    if (!$this->isApplicable($route_name)) {
      return $defaults;
    }

    if (!isset($defaults['group']) || !$defaults['group'] instanceof GroupInterface ||
      !$this->sectionsManager->isSectionsEnabled($defaults['group']->bundle())
    ) {
      throw new AccessDeniedHttpException(sprintf('Failed to find `group` argument in route named %s', $route_name));
    }

    // Check for parent section.
    if (!isset($defaults['section'])) {
      throw new \RuntimeException(sprintf('Failed to find `section` argument in route named %s', $route_name));
    }

    $section_path = $defaults['section'];
    if (!empty($defaults['sub_section'])) {
      $section_path .= '/' . $defaults['sub_section'];
    }

    // Try to find section or sub-section by their path.
    $section = $this->sectionsManager->getSectionFromGroupByPath($defaults['group'], $section_path);
    if ($section) {
      $defaults[$section->getEntityTypeId()] = $section;
    }

    return $defaults;
  }

  /**
   * Returns whether we should apply enhancer to given route name.
   *
   * @param string $route_name
   *   Machine route name.
   *
   * @return bool
   *   Result of route check.
   */
  protected function isApplicable(string $route_name) {
    return in_array($route_name, [
      LivingSpacesSectionsHtmlRouteProvider::SECTION_ROUTE,
      LivingSpacesSectionsHtmlRouteProvider::SUB_SECTION_ROUTE,

      // Sub-sections settings route use parent section path.
      'living_spaces_sections.sub_sections_form',
    ]);
  }

}
