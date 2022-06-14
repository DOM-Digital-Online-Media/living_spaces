<?php

namespace Drupal\living_spaces_group_privacy\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the LivingSpacesGroupPrivacy constraint.
 */
class LivingSpacesGroupPrivacyValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Provides config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Constructs a new GroupPrivacyValidator.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Defines the configuration object factory.
   */
  public function __construct(ConfigFactory $config) {
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'));
  }

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if ($object = $this->context->getObject()) {
      $bundle = $object->getEntity()->bundle();

      $value = '';
      foreach ($items as $item) {
        $value = !empty($item->value) ? $item->value : '';
      }

      $config = $this->config->getEditable('living_spaces_group_privacy.settings');
      if ($bundle && empty($value) && !empty($config->get('bundles')[$bundle])) {
        $this->context->addViolation($constraint->require);
      }
    }
  }

}
