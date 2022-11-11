<?php

namespace Drupal\living_spaces\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a site copyright block.
 *
 * @Block(
 *   id = "living_spaces_footer_logo_block",
 *   admin_label = @Translation("Footer Logo"),
 *   category = @Translation("Living Spaces")
 * )
 */
class LivingSpacesFooterLogoBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The file storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * Constructs a LivingSpacesPageTitleBlock block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $file_storage
   *   The file storage service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $file_storage) {
    $this->fileStorage = $file_storage;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('file')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['image'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://logo',
      '#title' => $this->t('Logo Image'),
      '#upload_validators' => [
        'file_validate_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
      ],
      '#default_value' => $this->configuration['image'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $image = $form_state->getValue('image');
    if (($image != $this->configuration['image']) && !empty($image[0]) && ($file = $this->fileStorage->load($image[0]))) {
      $file->setPermanent();
      $file->save();
      $this->configuration['image'] = $form_state->getValue('image');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [];
    $image = $this->configuration['image'];
    if (!empty($image[0]) && $file = $this->fileStorage->load($image[0])) {
      $build['content'] = [
        '#theme' => 'image',
        '#uri' => $file->getFileUri(),
      ];
    }
    return $build;
  }

}
