=== ConvertKit for WooCommerce ===
Contributors: nathanbarry, growdev, travisnorthcutt
Donate link: https://convertkit.com
Tags: email, marketing, embed form, convertkit, capture, woocommerce
Requires at least: 3.6
Tested up to: 5.4.0
Stable tag: 1.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrates WooCommerce with ConvertKit allowing customers to be automatically sent to your ConvertKit account.

== Description ==

[ConvertKit](https://convertkit.com) makes it easy to capture more leads and sell more products by easily
embedding email capture forms anywhere. This plugin makes it a little bit easier for those of us using WordPress
blogs, by automatically appending a lead capture form to any post or page.

== Installation ==

1. Download and unzip the plugin.
2. Upload `convertkit-for-woocommerce` to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.

== Configuration ==
1. Visit the plugin settings page by clicking on the Settings link under the plugin's name.
2. Enable or disable the plugin's functionality using the checkbox at the top of the page.
3. Use the Subscribe Event drop down to choose on which event customers will be subscribed.
4. Use the Display Opt-In Checkbox to subscribe customers only when they check box on Checkout page.
5. Select the text to be displayed on the Checkout page using the Opt-In Checkbox Label field.
6. Set the Opt-In Checkbox Default Stats as either Checked or Unchecked.
7. Set the location of the Opt-In Checkbox as either under the Billing fields, or Order details.
8. Enter your ConvertKit API key, which you can find [here](https://app.convertkit.com/account/edit), and save the settings
9. Enter your ConvertKit API Secret, which you can find [here](https://app.convertkit.com/account/edit).
10. Select the form, course, or tag customers will be subscribed to.
11. Select the format of the name that will be sent to ConvertKit's 'name' field.
12. Check the Purchases checkbox to send order data to ConvertKit's API
13. Enable or disable a debug log with the Debug checkbox.

== Frequently asked questions ==

= Does this plugin require a paid service? =

Yes, for it to work you must first have an account on ConvertKit.com

== Screenshots ==

1. Settings page
2. Checkout page with added checkbox

== Changelog ==

### 1.4.2 2022-xx-xx
* Added: Testing and compatibility for WooCommerce 6.0
* Added: PHP 8.x compatibility
* Added: Developers: Action and filter hooks.  See https://github.com/ConvertKit/convertkit-woocommerce/blob/1.4.2/ACTIONS-FILTERS.md
* Fix: Settings: Improved setting descriptions
* Fix: Settings: Only show conditional settings if other settings enabled/disabled
* Fix: Settings: API Key and Secret: Don't need to save settings twice for API Key and Secret to work
* Fix: Settings: Subscription: Renamed Courses to Sequences on Subscription dropdown option
* Fix: Settings: Purchase Data: If enabled, ensure that manually created orders send purchase data to ConvertKit 
* Fix: Settings: Purchase Data: If enabled, always send purchase data to ConvertKit, regardless of how the order is created or the payment method used
* Fix: Settings: Purchase Data: Once purchase data is sent to ConvertKit, don't keep sending it when e.g. the order's status changes
* Fix: WooCommerce Products: Renamed Courses to Sequences on Subscription dropdown option
* Fix: WooCommerce Checkout: Improved performance by not requesting ConvertKit Forms, Tags and Sequences from the API when not needed

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

none
