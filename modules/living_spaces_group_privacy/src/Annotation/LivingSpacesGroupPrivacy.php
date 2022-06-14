<?php

namespace Drupal\living_spaces_group_privacy\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a living_spaces_group_privacy annotation object.
 *
 * @Annotation
 */
class LivingSpacesGroupPrivacy extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The default state.
   *
   * @var bool
   */
  public $default;

}
