# donation_example
Donation example for Commerce 2.x

This module contains:
- An export of the described order item type, fields.
- The custom block
- The checkout pane

Note that for explicitness and clarity the block and checkout pane contain duplicate code. This code can be unified through a shared trait.

Requirements
------------
- Accept donations on a content page and on checkout
- Be able to select from predefined amounts, or enter a custom amount
- Be able to select frequency (one-time OR recurring)
- Be able to collect additional information in case the donation is a tribute:
  - Recipient name
  - Recipient email
  - Description

Order item type
---------------

We first need to define an order item type that will hold donations.
This order item type will not be backed by a product (Purchasable entity type: None),
It will contain fields for all relevant data.

Go to *admin/commerce/config/order-item-types* and click 'Add order item type'.

![Add order item type form](https://github.com/bojanz/donation_example/blob/master/add-order-item-type.png?raw=true)

Then go to the "Manage fields" screen and define the needed fields:
- Frequency (List - text)
- Tribute (Boolean)
- Recipient name (Text - plain)
- Recipient email (Email)
- Description (Text - plain, long)

The frequency field is required has the following values:
```
onetime|One time
monthly|Monthly
quarterly|Quarterly
annually|Annually
```
The other fields are optional.

Select or other
---------------
The module lives at https://www.drupal.org/project/select_or_other
Download and install the 8.x-1.x-dev version.

Donation block
--------------
Donations are created via the [donation block](https://github.com/bojanz/donation_example/blob/master/src/Plugin/Block/DonationBlock.php). There can be multiple instances of the donation block, shown on different pages. That means that the block is a good place to put settings on, for example the predefined amounts that will be offerred.

The donation block renders the [donation form](https://github.com/bojanz/donation_example/blob/master/src/Form/DonationForm.php) and optionally passes data to it (any settings, for example). The form is simple, it collects the needed information and then uses it to create an order item and add it to the cart.

Donation checkout pane
----------------------
The [checkout pane](https://github.com/bojanz/donation_example/blob/master/src/Plugin/Commerce/CheckoutPane/Donation.php) creates (and keeps up to date) a donation order item.

Recurring
---------
Recurring donations will be handled via custom code. This code will be triggered when the order is placed, by responding to the commerce_order.place.post_transition event. See the [event subscriber](https://github.com/bojanz/donation_example/blob/master/src/EventSubscriber/OrderSubscriber.php). 

Notes
-----
The cart (and the order summary on checkout) will not show a title for donation order items until this issue is fixed in Commerce: https://www.drupal.org/node/2855435
