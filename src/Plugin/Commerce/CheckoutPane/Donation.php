<?php

namespace Drupal\donation_example\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\Calculator;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the donation pane.
 *
 * @CommerceCheckoutPane(
 *   id = "donation",
 *   label = @Translation("Donation"),
 *   default_step = "order_information",
 *   wrapper_element = "fieldset",
 * )
 */
class Donation extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    // Hide the pane if there's already a donation order item?
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary() {
    $summary = [];
    if ($this->isVisible()) {
      $order_item = $this->getOrderItem();
      // Expand this to provide the appropriate output at checkout review.
      $summary = [
        '#plain_text' => $order_item->label(),
      ];
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $predefined_amounts = [
      '20' => '$20',
      '30' => '$30',
      '40' => '$40',
    ];
    $predefined_amount_keys = array_keys($predefined_amounts);
    $order_item = $this->getOrderItem();
    $unit_price = $order_item->getUnitPrice();
    $amount = $unit_price ? Calculator::trim($unit_price->getNumber()) : reset($predefined_amount_keys);

    $pane_form['frequency'] = [
      '#type' => 'radios',
      '#title' => t('Type'),
      '#options' => [
        'onetime' => t('One-time'),
        'monthly' => t('Monthly'),
        'quarterly' => t('Quarterly'),
        'annually' => t('Annually'),
      ],
      '#default_value' => $order_item->field_frequency->value,
      '#required' => TRUE,
    ];
    $pane_form['amount'] = [
      '#type' => 'select_or_other_buttons',
      '#title' => t('Amount'),
      '#options' => $predefined_amounts,
      '#default_value' => $amount,
      '#required' => TRUE,
    ];
    $pane_form['tribute'] = [
      '#type' => 'checkbox',
      '#title' => t('This is a tribute'),
      '#default_value' => $order_item->field_tribute->value,
    ];
    $pane_form['recipient_name'] = [
      '#type' => 'textfield',
      '#title' => t('Recipient name'),
      '#default_value' => $order_item->field_recipient_name->value,
      '#states' => [
        'visible' => [
          ':input[name="donation[tribute]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $pane_form['recipient_email'] = [
      '#type' => 'email',
      '#title' => t('Recipient email'),
      '#default_value' => $order_item->field_recipient_email->value,
      '#states' => [
        'visible' => [
          ':input[name="donation[tribute]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $pane_form['description'] = [
      '#type' => 'textarea',
      '#title' => t('Description'),
      '#default_value' => $order_item->field_description->value,
      '#states' => [
        'visible' => [
          ':input[name="donation[tribute]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    $amount = $values['amount'][0];
    if (!is_numeric($amount)) {
      $form_state->setError($pane_form['amount'], t('The amount must be a valid number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $order_item = $this->getOrderItem();
    $values = $form_state->getValue($pane_form['#parents']);
    $amount = $values['amount'][0];

    $order_item->title = t('$@amount donation', ['@amount' => $amount]);
    $order_item->unit_price = ['number' => $amount, 'currency_code' => 'USD'];
    $order_item->field_frequency = $values['frequency'];
    $order_item->field_tribute = $values['tribute'];
    $order_item->field_recipient_name = $values['recipient_name'];
    $order_item->field_recipient_email = $values['recipient_email'];
    $order_item->field_description = $values['description'];
    $order_item->save();
    if (!$this->order->hasItem($order_item)) {
      $this->order->addItem($order_item);
    }
  }

  /**
   * Gets the donation order item.
   *
   * If one isn't found, it will be created.
   *
   * @return \Drupal\commerce_order\Entity\OrderItemInterface
   *   The donation order item.
   */
  protected function getOrderItem() {
    $donation_order_item = NULL;
    // Try to find an existing order item.
    foreach ($this->order->getItems() as $order_item) {
      if ($order_item->bundle() == 'donation') {
        $donation_order_item = $order_item;
        break;
      }
    }
    if (!$donation_order_item) {
      // None found, create a new one.
      $donation_order_item = OrderItem::create([
        'type' => 'donation',
      ]);
    }

    return $donation_order_item;
  }

}
