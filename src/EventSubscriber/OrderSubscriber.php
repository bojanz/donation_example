<?php

namespace Drupal\donation_example\EventSubscriber;

use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'commerce_order.place.post_transition' => ['onPlace'],
    ];
  }

  /**
   * Starts a billing cycle for recurring donations.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function onPlace(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    foreach ($order->getItems() as $order_item) {
      if ($order_item->bundle() == 'donation') {
        $frequency = $order_item->field_frequency->value;
        if ($frequency != 'onetime') {
          // This is a recurring donation order item, do something with it.
        }
      }
    }
  }

}
