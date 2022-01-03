<?php
/**
 * ConvertKit Checkout class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Registers a metabox on WooCommerce Products
 * and saves its settings when the Product is saved in the WordPress Administration
 * interface.
 *
 * @package CKWC
 * @author ConvertKit
 */
class CKWC_Checkout {

	/**
	 * Holds the WooCommerce Integration instance for this Plugin.
	 * 
	 * @since 	1.4.2
	 *
	 * @var 	WC_Integration
	 */
	private $integration;

	/**
	 * Constructor
	 * 
	 * @since 	1.0.0
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
		}

		// Store whether the customer should be opted in, in the Order's metadata.
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_opt_in_checkbox' ), 10, 1 );
		
	}

	/**
	 * Adds the opt-in checkbox to the checkout's billing or order section, based
	 * on the Plugin's settings.
	 * 
	 * @since 	1.0.0
	 * 
	 * @param 	array 	$fields 	Checkout Fields.
	 * @return 	array 				Checkout Fields
	 */
	public function add_opt_in_checkbox( $fields ) {

		$fields[ $this->integration->get_option( 'opt_in_location' ) ]['ckwc_opt_in'] = array(
			'type'    => 'checkbox',
			'label'   => $this->integration->get_option( 'opt_in_label' ),
			'default' => ( $this->integration->get_option( 'opt_in_status' ) == 'checked' ? 'checked' : '' ),
		);

		return $fields;

	}

	/**
	 * Saves whether the customer should be subscribed to ConvertKit for this order.
	 * 
	 * This function is not called if the 'Subscribe Customers' option is disabled
	 * in the Plugin settings.
	 * 
	 * @since 	1.0.0
	 * 
	 * @param 	int 	$order_id 	WooCommerce Order ID.
	 */
	public function save_opt_in_checkbox( $order_id ) {

		// Don't opt in by default.
		$opt_in = 'no';

		// If no opt in checkbox is displayed, opt in.
		if ( ! $this->integration->get_option_bool( 'display_opt_in' ) ) {
			$opt_in = 'yes';
		} else {
			// Opt in checkbox is displayed at checkout.
			// Opt in if it is checked.
			if ( isset( $_POST['ckwc_opt_in'] ) ) {
				$opt_in = 'yes';
			}
		}

		// Update Order Post Meta.
		update_post_meta( $order_id, 'ckwc_opt_in', $opt_in );

	}

}