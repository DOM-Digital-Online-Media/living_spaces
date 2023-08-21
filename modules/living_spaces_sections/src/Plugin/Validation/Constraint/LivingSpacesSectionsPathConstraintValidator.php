<?php

namespace Drupal\living_spaces_sections\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\living_spaces_sections\LivingSpacesSectionsHtmlRouteProvider;
use Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the LivingSpacesSectionsPathConstraint constraint.
 */
class LivingSpacesSectionsPathConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Route provider service.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RouteProviderInterface $route_provider) {
    $this->entityTypeManager = $entity_type_manager;
    $this->routeProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('router.route_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    /** @var \Drupal\living_spaces_sections\Plugin\Validation\Constraint\LivingSpacesSectionsPathConstraint $constraint */

    foreach ($items as $item) {
      if (empty($item->value)) {
        $this->context->addViolation($constraint->isEmpty);
      }

      if (!$this->isUnique($item->value)) {
        $this->context->addViolation($constraint->notUnique, ['%value' => $item->value]);
      }
    }
  }

  /**
   * Checks if path is unique and there is no such page on a group.
   *
   * @param string $value
   *   Path value.
   *
   * @return bool
   *   TRUE if the path is unique, FALSE otherwise.
   */
  private function isUnique($value) {
    /** @var \Drupal\living_spaces_sections\Entity\LivingSpacesSectionInterface $section */
    $section = $this->context->getObject()->getEntity();
    if ($section instanceof LivingSpacesSectionInterface) {

      // Check if we have any section with the same path for the group.
      if ($group = $section->getGroup()) {
        $group_sections = array_column(
          $group->get(LIVING_SPACES_SECTIONS_FIELD)->getValue(),
          'target_id'
        );

        // Filter out current section that is being edited.
        $group_sections = array_filter($group_sections, function ($section_id) use ($section) {
          return $section->isNew() || ($section_id !== $section->id());
        });

        if (!empty($group_sections)) {
          $section_count = $this->entityTypeManager->getStorage('living_spaces_section')
            ->getQuery()
            ->condition('id', $group_sections, 'IN')
            ->condition('path', $value)
            ->accessCheck()
            ->count()
            ->execute();

          if ($section_count > 0) {
            return FALSE;
          }
        }
      }

      // Check if we have page from contrib module/core by that path.
      if ($parent = $section->getParentSection()) {
        $path = '/group/{group}/' . $parent->bundle() . '/' . $value;
        $route = LivingSpacesSectionsHtmlRouteProvider::SUB_SECTION_ROUTE;
      }
      else {
        $path = '/group/{group}/' . $value;
        $route = LivingSpacesSectionsHtmlRouteProvider::SECTION_ROUTE;
      }
      $routes = $this->routeProvider->getRoutesByPattern($path);
      foreach ($routes->getIterator() as $id => $matching_route) {
        if ($id !== $route) {
          return FALSE;
        }
      }
    }

    return TRUE;
  }

}
