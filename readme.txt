=== ConvertKit for WooCommerce ===
Contributors: nathanbarry, davidlamarwheeler, growdev, nickohrn
Donate link: https://convertkit.com
Tags: email, marketing, embed form, convertkit, capture, woocommerce
Requires at least: 3.6
Tested up to: 4.5.3
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrates WooCommerce with ConvertKit allowing customers to be automatically sent to your ConvertKit account.

== Description ==

[ConvertKit](https://convertkit.com) makes it easy to capture more leads and sell more products by easily
embedding email capture forms anywhere. This plugin makes it a little bit easier for those of us using WordPress
blogs, by automatically appending a lead capture form to any post or page.

== Installation ==

1. Upload `woocommerce-convertkit` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit the settings page by clicking on the link under the plugin's name.
4. Enable or disable the plugin's functionality using the checkbox at the top of the page.
5. Use the Subscribe Event drop down to choose on which event customers will be subscribed.
6. Select the text to be displayed on the Checkout page using the Opt-In Checkbox Label field.
7. Set the Opt-In Checkbox Defaut Stats as either Checked or Unchecked.
8. Set the location of the Opt-In Checkbox as either under the Billing fields, or Order details.
9. Enter your ConvertKit API key, which you can find [here](https://app.convertkit.com/account/edit), and save the settings
10. Select the form customers will be subscribed to.
11. Select the format of the name that will be sent to ConvertKit's 'name' field.
12. Enable or disable a debug log.

== Frequently asked questions ==

= Does this plugin require a paid service? =

Yes, for it to work you must first have an account on ConvertKit.com

== Screenshots ==

1. Settings page
2. Checkout page with added checkbox

== Changelog ==

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
