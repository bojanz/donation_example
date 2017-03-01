<?php

namespace Drupal\donation_example\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
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
    // Hide the pane if there's already a donation order item.
    foreach ($this->order->getItems() as $order_item) {
      if ($order_item->bundle() == 'donation') {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary() {
    // Expand this to provide the appropriate output at checkout review.
    return [
      '#plain_text' => '',
    ];
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
    $selected_amount = reset($predefined_amount_keys);

    $pane_form['frequency'] = [
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
    $pane_form['amount'] = [
      '#type' => 'select_or_other_buttons',
      '#title' => t('Amount'),
      '#options' => $predefined_amounts,
      '#default_value' => $selected_amount,
      '#required' => TRUE,
    ];
    $pane_form['tribute'] = [
      '#type' => 'checkbox',
      '#title' => t('This is a tribute'),
      '#default_value' => FALSE,
    ];
    $pane_form['recipient_name'] = [
      '#type' => 'textfield',
      '#title' => t('Recipient name'),
      '#states' => [
        'visible' => [
          ':input[name="donation[tribute]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $pane_form['recipient_email'] = [
      '#type' => 'email',
      '#title' => t('Recipient email'),
      '#states' => [
        'visible' => [
          ':input[name="donation[tribute]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $pane_form['description'] = [
      '#type' => 'textarea',
      '#title' => t('Description'),
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
    $values = $form_state->getValue($pane_form['#parents']);
  }

}
