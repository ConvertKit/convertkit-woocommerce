=== ConvertKit for WooCommerce ===
Contributors: nathanbarry, growdev, travisnorthcutt, convertkit
Donate link: https://convertkit.com
Tags: email, marketing, embed form, convertkit, capture, woocommerce
Requires at least: 5.0
Tested up to: 5.9
Requires PHP: 5.6.20
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrates WooCommerce with ConvertKit allowing customers to be automatically sent to your ConvertKit account.

== Description ==

[ConvertKit](https://convertkit.com) makes it easy to capture more leads and sell more products by easily embedding email capture forms anywhere.

This Plugin integrates WooCommerce with ConvertKit, allowing customers and purchase data to be automatically sent to your ConvertKit account.

Full plugin documentation is located [here](https://help.convertkit.com/en/articles/2502554-woocommerce-integration).

== Installation ==

1. Upload the `convertkit-for-woocommerce` folder to the `/wp-content/plugins/` directory
2. Active the ConvertKit for WooCommerce plugin through the 'Plugins' menu in WordPress

== Configuration ==

1. Configure the plugin by navigating to WooCommerce > Settings > in the WordPress Administration Menu, then click the Integration tab > ConvertKit
2. Enable the integration
3. Enter your [API Key](https://app.convertkit.com/account_settings/advanced_settings) and API Secret, clicking Save changes
4. Choose the Subscription Form, Tag or Sequence to subscribe Customers to when they complete the WooCommerce Checkout
5. Configure other settings as necessary, depending on your requirements
6. (Optional) Configure which Sequence, Form or Tag to subscribe a Customer to when editing individual WooCommerce Products

== Frequently asked questions ==

= Does this plugin require a paid service? =

No. You must first have an account on ConvertKit.com, but you do not have to use a paid plan!

== Screenshots ==

1. Settings page
2. Checkout page with added checkbox

== Changelog ==

### 1.4.4 2022-03-10
* Fix: Include Name when subscribing to a Tag or Sequence
* Fix: Settings: Change Subscribe Event's "Order Created" label to "Order Pending payment", to reflect WooCommerce Order's status labels
* Fix: WooCommerce Subscriptions: Don't resubscribe customer when a WooCommerce Order created is for a subscription renewal

### 1.4.3 2022-02-14
* Added: Settings: Options to Map WooCommerce Order Data (Phone, Billing Address etc) to ConvertKit Custom Fields
* Added: Select2 dropdown for Forms, Tags and Sequence selection with search functionality for improved UX.

### 1.4.2 2022-01-28
* Added: Testing and compatibility for WooCommerce 6.1
* Added: PHP 8.x compatibility
* Added: Developers: Action and filter hooks.  See https://github.com/ConvertKit/convertkit-woocommerce/blob/master/ACTIONS-FILTERS.md
* Added: Localization and .pot file for translators
* Fix: Settings: Only show conditional settings if other settings enabled/disabled
* Fix: Settings: API Key and Secret: Don't need to save settings twice for API Key and Secret to work
* Fix: Settings: Improved setting descriptions
* Fix: Settings: Improved order and layout of settings to be more logical
* Fix: Settings: Subscription: Renamed Courses to Sequences on Subscription dropdown option
* Fix: Settings: Purchase Data: If enabled, always send purchase data to ConvertKit, regardless of how the order is created or the payment method used
* Fix: Settings: Purchase Data: Once purchase data is sent to ConvertKit, don't keep sending it when e.g. the order's status changes
* Fix: WooCommerce: Order: Don't subscribe a Customer a second time if an existing Order's status changes back to the Plugin's Subscribe Event
* Fix: WooCommerce: Products: Renamed Courses to Sequences on Subscription dropdown option
* Fix: WooCommerce: Checkout: Improved performance by not requesting ConvertKit Forms, Tags and Sequences from the API when not needed
* Fix: Performance: Cache Forms, Tags and Sequences from ConvertKit account for longer than 5 minutes, to prevent API timeouts and slow loading in the WordPress Administration

### 1.4.1  2020-06-06
* Protect against missing products on order items

### 1.4.0  2020-04-03
* Add support for sending manual orders to ConvertKit

### 1.3.0  2019-12-20
* Add support for sending cash on delivery and check payment purchase info to ConvertKit

### 1.2.0  2019-07-15
* Improve plugin's translation readiness
* Make plugin settings link work with other WooCommerce addons
* Correct product metabox title
* Display tags/forms/sequences alphabetically
* Add ability to force-refresh subscription options
* Add integration name to purchase API requests

### 1.1.0  2019-03-15
* Add WooCommerce order note when a customer is subscribed to ConvertKit

### 1.0.6  2018-09-10
* Fix for cart item_id being sent to ConvertKit's API instead of product_id. This was causing product purchases to be seen as unique instead of grouped.

### 1.0.5  2018-07-24
* Added ability for WooCommerce purchase data to be sent to Seva's API
* See: http://developers.convertkit.com/#purchases
* Updated Installation and Configuration sections of this readme.

### 1.0.4  2017-08-09
* Verified compatibility with WooCommerce 3.1
* Code cleanup.

### 1.0.3
* Added Settings plugin link.
* Added a setting to allow admin to decide if First Name, Last Name, or both are sent to CK's 'name' field.

### 1.0.2
* Added logger to help debug connectivity issues.

### 1.0.1
* Don't use API when setting transient options

### 1.0.0
* Initial release

== Upgrade notice ==

