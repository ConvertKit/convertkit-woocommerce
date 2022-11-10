<?php
/**
 * ConvertKit WooCommerce Subscriptions Plugin class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Adds support for the WooCommerce Subscriptions Plugin, preventing Orders
 * created by the Subscriptions Plugin that are for subscription renewals,
 * cancellations or plan changes from opting in the Customer again to ConvertKit.
 *
 * @package CKWC
 * @author ConvertKit
 */
class CKWC_WC_Subscriptions {

	/**
	 * The third party Plugin folder and filename that must exist
	 * and be active in WordPress for this classes code to function.
	 *
	 * @since   1.4.4
	 *
	 * @var     string
	 */
	private $plugin_folder_filename = 'woocommerce-subscriptions/woocommerce-subscriptions.php';

	/**
	 * The minimum version the third party Plugin must be for this
	 * classes code to function.
	 *
	 * @since   1.4.4
	 *
	 * @var     string
	 */
	private $minimum_supported_version = '1.0.0';

	/**
	 * The third party Plugin functions that must exist for this classes code
	 * to function.
	 *
	 * @since   1.4.4
	 *
	 * @var     array
	 */
	private $required_plugin_functions = array(
		'wcs_order_contains_subscription',
		'wcs_order_contains_renewal',
		'wcs_order_contains_resubscribe',
		'wcs_order_contains_switch',
	);

	/**
	 * Constructor
	 *
	 * @since   1.4.4
	 */
	public function __construct() {

		// Check if the Order should opt in the Customer.
		add_filter( 'convertkit_for_woocommerce_order_should_opt_in_customer', array( $this, 'order_should_opt_in_customer' ), 10, 2 );

	}

	/**
	 * Checks if the Order was created by the WooCommerce Subscriptions Plugin, and if so,
	 * that it is an Order for a new subscription.
	 *
	 * If the Order is not for a new subscription, the customer is not opted in again to ConvertKit,
	 * preventing customers incorrectly being resubscribed to ConvertKit on e.g. a WooCommerce
	 * Subscription Payment Renewal.
	 *
	 * @since   1.4.4
	 *
	 * @param   bool $should_opt_in_customer     Should opt in Customer.
	 * @param   int  $order_id                   Order ID.
	 * @return  bool                                Should opt in Customer.
	 */
	public function order_should_opt_in_customer( $should_opt_in_customer, $order_id ) {

		// Return original opt in status if WooCommerce Subscriptions isn't active.
		if ( ! $this->is_plugin_active() ) {
			return $should_opt_in_customer;
		}

		// Return original opt in status if Order was not created by WooCommerce Subscriptions.
		if ( ! wcs_order_contains_subscription( $order_id, 'any' ) ) {
			return $should_opt_in_customer;
		}

		// Do not opt in if the Order is a WooCommerce Subscription renewal, resubscription or plan switch.
		if ( wcs_order_contains_renewal( $order_id ) ||
			wcs_order_contains_resubscribe( $order_id ) ||
			wcs_order_contains_switch( $order_id ) ) {
			return false;
		}

		// If here, Order is a new WooCommerce Subscription.
		// Return original opt in status.
		return $should_opt_in_customer;

	}

	/**
	 * Checks if the third party Plugin is active, and if it meets the minimum supported
	 * version, if specified.
	 *
	 * @since   1.4.4
	 *
	 * @return  bool    Plugin is Active
	 */
	public function is_plugin_active() {

		// Load is_plugin_active() function if not available i.e. this is a cron request.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// If we still can't use WordPress' function, assume third party Plugin isn't active.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			return false;
		}

		// Bail if the third party Plugin isn't active.
		if ( ! is_plugin_active( $this->plugin_folder_filename ) ) {
			return false;
		}

		// If the third party Plugin doesn't match the minimum supported version, deem it as not active.
		if ( ! version_compare( $this->get_version(), $this->minimum_supported_version, '>=' ) ) {
			return false;
		}

		// If function(s) are required for this third party Plugin, check they exist now.
		if ( $this->required_plugin_functions ) {
			foreach ( $this->required_plugin_functions as $function ) {
				if ( ! function_exists( $function ) ) {
					return false;
				}
			}
		}

		// If here, the third party Plugin is active and meets the minimum supported version.
		return true;

	}

	/**
	 * Returns the third party Plugin's version number.
	 *
	 * @since   1.4.4
	 *
	 * @return  string     Version
	 */
	private function get_version() {

		$plugin_data = get_file_data( WP_PLUGIN_DIR . '/' . $this->plugin_folder_filename, array( 'Version' => 'Version' ) );
		return $plugin_data['Version'];

	}

}
