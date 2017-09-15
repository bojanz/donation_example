<?php

namespace Drupal\donation_example\Form;

use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the donation form.
 */
class DonationForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The current store.
   *
   * @var \Drupal\commerce_store\CurrentStoreInterface
   */
  protected $currentStore;

  /**
   * Constructs a new DonationForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\commerce_store\CurrentStoreInterface $current_store
   *   The current store.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CartManagerInterface $cart_manager, CartProviderInterface $cart_provider, CurrentStoreInterface $current_store) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cartManager = $cart_manager;
    $this->cartProvider = $cart_provider;
    $this->currentStore = $current_store;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('commerce_store.current_store')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'donation_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $predefined_amounts = [
      '20' => '$20',
      '30' => '$30',
      '40' => '$40',
    ];
    $predefined_amount_keys = array_keys($predefined_amounts);
    $selected_amount = reset($predefined_amount_keys);

    $form['frequency'] = [
      '#type' => 'radios',
      '#title' => t('Type'),
      '#options' => [
        'onetime' => t('One-time'),
        'monthly' => t('Monthly'),
        'quarterly' => t('Quarterly'),
        'annually' => t('Annually'),
      ],
      '#default_value' => 'onetime',
      '#required' => TRUE,
    ];
    $form['amount'] = [
      '#type' => 'select_or_other_buttons',
      '#title' => t('Amount'),
      '#options' => $predefined_amounts,
      '#default_value' => $selected_amount,
      '#required' => TRUE,
    ];
    $form['tribute'] = [
      '#type' => 'checkbox',
      '#title' => t('This is a tribute'),
      '#default_value' => FALSE,
    ];
    $form['recipient_name'] = [
      '#type' => 'textfield',
      '#title' => t('Recipient name'),
      '#states' => [
        'visible' => [
          ':input[name="tribute"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['recipient_email'] = [
      '#type' => 'email',
      '#title' => t('Recipient email'),
      '#states' => [
        'visible' => [
          ':input[name="tribute"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => t('Description'),
      '#states' => [
        'visible' => [
          ':input[name="tribute"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Donate'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $amount = $form_state->getValue('amount')[0];
    if (!is_numeric($amount)) {
      $form_state->setError($form['amount'], t('The amount must be a valid number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $amount = $form_state->getValue('amount')[0];
    $order_item = $this->entityTypeManager->getStorage('commerce_order_item')->create([
      'type' => 'donation',
      'title' => t('$@amount donation', ['@amount' => $amount]),
      'unit_price' => [
        'number' => $amount, 
        'currency_code' => 'USD',
      ],
      'field_frequency' => $form_state->getValue('frequency'),
      'field_tribute' => $form_state->getValue('tribute'),
      'field_recipient_name' => $form_state->getValue('recipient_name'),
      'field_recipient_email' => $form_state->getValue('recipient_email'),
      'field_description' => $form_state->getValue('description'),
    ]);
    $store = $this->currentStore->getStore();
    // Always use the 'default' order type.
    $cart = $this->cartProvider->getCart('default', $store);
    if (!$cart) {
      $cart = $this->cartProvider->createCart('default', $store);
    }
    $this->cartManager->addOrderItem($cart, $order_item, FALSE);

    // Go to checkout.
    $form_state->setRedirect('commerce_checkout.form', ['commerce_order' => $cart->id()]);
  }

}
