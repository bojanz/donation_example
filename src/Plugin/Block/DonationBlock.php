<?php

namespace Drupal\donation_example\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\donation_example\Form\DonationForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a donation block.
 *
 * @Block(
 *   id = "donation",
 *   admin_label = @Translation("Donation"),
 *   category = @Translation("Commerce")
 * )
 */
class DonationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a new CartBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * Builds the donation form.
   *
   * @return array
   *   A render array.
   */
  public function build() {
    // Could use block settings to get the predefined amounts to show, pass
    // them to getForm() as the second argument.
    return $this->formBuilder->getForm(DonationForm::class);
  }

}
