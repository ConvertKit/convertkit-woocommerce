<?php
/**
 * ConvertKit Checkout class.
 *
 * @package CKWC
 * @author ConvertKit
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Determines whether to display an opt in checkout on the WooCommerce Checkout,
 * based on the integration's settings, and stores whether the WooCommerce Order
 * created through the Checkout should opt the customer into ConvertKit.
 *
 * @package CKWC
 * @author ConvertKit
 */
class CKWC_Checkout {

	/**
	 * Holds the WooCommerce Integration instance for this Plugin.
	 *
	 * @since   1.4.2
	 *
	 * @var     CKWC_Integration
	 */
	private $integration;

	/**
	 * Constructor
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		// Fetch integration.
		$this->integration = WP_CKWC_Integration();

		// If the integration isn't enabled, don't load any other actions or filters.
		if ( ! $this->integration->is_enabled() ) {
			return;
		}

		// If Display Opt In is enabled, show the Opt In checkbox at Checkout.
		if ( $this->integration->get_option_bool( 'display_opt_in' ) ) {
			add_filter( 'woocommerce_checkout_fields', array( $this, 'add_opt_in_checkbox' ) );

			// Extend Store API endpoint to support storing the value of the opt in checkbox block.
	        woocommerce_store_api_register_endpoint_data(
	            array(
	                'endpoint'        => \Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema::IDENTIFIER,
	                'namespace'       => 'ckwc-opt-in',
	                'schema_callback' => array( $this, 'schema' ),
	                'schema_type'     => ARRAY_A,
	            )
	        );
		}

		// Store whether the customer should be opted in, in the Order's metadata, when using the Checkout shortcode.
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_opt_in_checkbox' ), 10, 1 );

		// Store whether the customer should be opted in, in the Order's metadata, when using the Checkout block.
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'save_opt_in_checkbox_block' ), 10, 2 );

	}

	/**
	 * Defines the data schema for the opt in block.
	 * 
	 * @since 	1.7.1
	 * 
	 * @TODO This needs to be moved, really.
	 * 
	 * @return 	array
	 */
	public function schema() {

		return array(
			'ckwc_opt_in' => array(
				'description' => 'foo',
				'type' => 'boolean',
				'context' => array( 'view', 'edit' ),
				'optional' => true,
				'arg_options' => array(
					'validate_callback' => function( $value ) {
						return true;
					},
				),
			),
		);

	}

	/**
	 * Adds the opt-in checkbox to the checkout's billing or order section, based
	 * on the Plugin's settings.
	 *
	 * @since   1.0.0
	 *
	 * @param   array $fields     Checkout Fields.
	 * @return  array               Checkout Fields
	 */
	public function add_opt_in_checkbox( $fields ) {

		$fields[ $this->integration->get_option( 'opt_in_location' ) ]['ckwc_opt_in'] = array(
			'type'    => 'checkbox',
			'label'   => $this->integration->get_option( 'opt_in_label' ),
			'default' => 'checked' === $this->integration->get_option( 'opt_in_status' ),
		);

		/**
		 * Adds the opt-in checkbox to the checkout's billing or order section, based
		 * on the Plugin's settings.
		 *
		 * @since   1.0.0
		 *
		 * @param   array $fields     Checkout Fields.
		 * @return  array               Checkout Fields
		 */
		$fields = apply_filters( 'convertkit_for_woocommerce_checkout_add_opt_in_checkbox', $fields );

		// Return.
		return $fields;

	}

	/**
	 * Saves whether the customer should be subscribed to ConvertKit for this order
	 * when using the checkout.
	 *
	 * This function is not called if the 'Subscribe Customers' option is disabled
	 * in the Plugin settings.
	 *
	 * @since   1.0.0
	 *
	 * @param   int $order_id   WooCommerce Order ID.
	 */
	public function save_opt_in_checkbox( $order_id ) {

		$this->save_opt_in_for_order( 
			wc_get_order( $order_id ),
			isset( $_POST['ckwc_opt_in'] )
		);

	}

	public function save_opt_in_checkbox_block( $order, $request ) {

		$this->save_opt_in_for_order(
			$order,
			(bool) $request['extensions']['ckwc-opt-in']['ckwc_opt_in']
		);

	}

	private function save_opt_in_for_order( $order, $checkbox_checked = false ) {

		// Bail if the given Order ID isn't for a WooCommerce Order.
		// Third party Plugins e.g. WooCommerce Subscriptions may call the `woocommerce_checkout_update_order_meta`
		// action with a non-Order ID, resulting in inadvertent opt ins.
		if ( OrderUtil::get_order_type( $order->get_id() ) !== 'shop_order' ) {
			return;
		}

		// Don't opt in by default.
		$opt_in = 'no';

		// If no opt in checkbox is displayed, opt in.
		if ( ! $this->integration->get_option_bool( 'display_opt_in' ) ) {
			$opt_in = 'yes';
		} elseif ( $checkbox_checked ) {
			// Opt in checkbox is displayed at checkout and was checked.
			$opt_in = 'yes';
		}

		// Update Order Post Meta.
		$order->update_meta_data( 'ckwc_opt_in', $opt_in );
		$order->save();

	}

}
