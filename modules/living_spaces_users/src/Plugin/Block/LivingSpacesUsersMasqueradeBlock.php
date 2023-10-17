<?php

namespace Drupal\living_spaces_users\Plugin\Block;

use Drupal\masquerade\Plugin\Block\MasqueradeBlock;
use Drupal\masquerade\Form\MasqueradeForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'User masquerade' block.
 *
 * @Block(
 *   id = "living_spaces_users_masquerade",
 *   admin_label = @Translation("User masquerade"),
 *   category = @Translation("Living Spaces"),
 * )
 */
class LivingSpacesUsersMasqueradeBlock extends MasqueradeBlock {

  /**
   * Returns the current_user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );

    $instance->formBuilder = $container->get('form_builder');
    $instance->masquerade = $container->get('masquerade');
    $instance->currentUser = $container->get('current_user');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($this->configuration['show_unmasquerade_link'] && $this->masquerade->isMasquerading()) {
      return [
        [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $this->t('You are masquerading as <b>@user</b>.', [
            '@user' => $this->currentUser->getDisplayName(),
          ]),
        ],
        [
          '#lazy_builder' => ['masquerade.callbacks:renderSwitchBackLink', []],
          '#create_placeholder' => TRUE,
        ],
      ];
    }

    return $this->formBuilder->getForm(MasqueradeForm::class);
  }

}
