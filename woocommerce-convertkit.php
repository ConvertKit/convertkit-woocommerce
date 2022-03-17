<?php
/**
 * ConvertKit for WooCommerce Plugin.
 *
 * @package CKWC
 * @author ConvertKit
 *
 * @wordpress-plugin
 * Plugin Name: ConvertKit for WooCommerce
 * Plugin URI:  https://www.convertkit.com
 * Description: Integrates WooCommerce with ConvertKit, allowing customers to be automatically sent to your ConvertKit account.
 * Version: 1.4.5
 * Author: ConvertKit
 * Author URI: https://www.convertkit.com
 * Text Domain: woocommerce-convertkit
 *
 * WC requires at least: 3.0
 * WC tested up to: 6.3.1
 */

// Bail if Plugin is already loaded.
if ( class_exists( 'WP_CKWC' ) ) {
	return;
}

// Define ConverKit Plugin paths and version number.
define( 'CKWC_PLUGIN_FILE', plugin_basename( __FILE__ ) );
define( 'CKWC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CKWC_PLUGIN_PATH', __DIR__ );
define( 'CKWC_PLUGIN_VERSION', '1.4.5' );

// Load files that are always used.
require_once CKWC_PLUGIN_PATH . '/includes/functions.php';
require_once CKWC_PLUGIN_PATH . '/includes/class-wp-ckwc.php';
require_once CKWC_PLUGIN_PATH . '/includes/class-ckwc-api.php';
require_once CKWC_PLUGIN_PATH . '/includes/class-ckwc-checkout.php';
require_once CKWC_PLUGIN_PATH . '/includes/class-ckwc-order.php';
require_once CKWC_PLUGIN_PATH . '/includes/class-ckwc-resource.php';
require_once CKWC_PLUGIN_PATH . '/includes/class-ckwc-resource-custom-fields.php';
require_once CKWC_PLUGIN_PATH . '/includes/class-ckwc-resource-forms.php';
require_once CKWC_PLUGIN_PATH . '/includes/class-ckwc-resource-sequences.php';
require_once CKWC_PLUGIN_PATH . '/includes/class-ckwc-resource-tags.php';
require_once CKWC_PLUGIN_PATH . '/includes/class-ckwc-review-request.php';
require_once CKWC_PLUGIN_PATH . '/includes/class-ckwc-wc-subscriptions.php';

// Load files that are only used in the WordPress Administration interface.
if ( is_admin() ) {
	require_once CKWC_PLUGIN_PATH . '/admin/class-ckwc-admin-plugin.php';
	require_once CKWC_PLUGIN_PATH . '/admin/class-ckwc-admin-product.php';
}

/**
 * Main function to return Plugin instance.
 *
 * @since   1.4.2
 */
function WP_CKWC() { // phpcs:ignore

	return WP_CKWC::get_instance();

}

/**
 * Main function to return the WooCommerce Integration class.
 *
 * @since   1.0.0
 */
function WP_CKWC_Integration() { // phpcs:ignore

	// Bail if WooCommerce isn't active.
	if ( ! function_exists( 'WC' ) ) {
		return false;
	}

	// Get registered WooCommerce integrations.
	$integrations = WC()->integrations->get_integrations();

	// Return our integration, if it's registered.
	return isset( $integrations['ckwc'] ) ? $integrations['ckwc'] : false;

}

// Finally, initialize the Plugin.
WP_CKWC();
