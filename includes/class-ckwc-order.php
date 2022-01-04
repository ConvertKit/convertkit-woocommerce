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
class CKWC_Order {

	/**
	 * Holds the WooCommerce Integration instance for this Plugin.
	 * 
	 * @since 	1.4.2
	 *
	 * @var 	WC_Integration
	 */
	private $integration;

	/**
	 * Holds the ConvertKit API.
	 * 
	 * @since 	1.4.2
	 * 
	 * @var 	CKWC_API
	 */
	private $api;

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

		// Subscribe customer's email address to a form or tag.
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'order_status' ), 99999, 1 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'order_status' ), 99999, 3 );

		// Send Purchase Data.
		if ( $this->integration->get_option_bool( 'send_purchases' ) ) {
			add_action( 'woocommerce_order_status_changed', array( $this, 'send_purchase_data' ), 99999, 1 );
		}
		
	}

	/**
	 * Subscribe the customer's email address to a ConvertKit Form, Tag or Sequence, if the Order's event
	 * matches the Subscribe Event in this Plugin's Settings.
	 * 
	 * @since 	1.0.0
	 * 
	 * @param 	int 	$order_id 	WooCommerce Order ID
	 * @param 	string 	$status_old Order's Old Status
	 * @param 	string 	$status_new Order's New Status
	 */
	public function order_status( $order_id, $status_old = 'new', $status_new = 'pending' ) {

		// Bail if the Subscribe Event doesn't match the Order's new status.
		if ( $this->integration->get_option( 'event' ) != $status_new ) {
			return;
		}

		// Bail if the Order does not require that we subscribe the Customer.
		if ( ! $this->should_opt_in_customer( $order_id ) ) {
			return;
		}

		// Get WooCommerce Order.
		$order = wc_get_order( $order_id );

		// If no Order could be fetched, bail.
		if ( ! $order ) {
			return;
		}

		// Build an array of Forms and Tags to subscribe the Customer to, based on
		// the global integration settings and any Product-specific settings.
		$subscriptions = array( $this->integration->get_option( 'subscription' ) );
		foreach ( $order->get_items() as $item ) {
			$subscriptions[] = get_post_meta( $item['product_id'], 'ckwc_subscription', true );
		}

		// Remove any duplicate Forms and Tags.
		$subscriptions = array_filter( array_unique( $subscriptions ) );

		// Setup the API.
		$this->api = new CKWC_API( 
			$this->integration->get_option( 'api_key' ),
			$this->integration->get_option( 'api_secret' ),
			$this->integration->get_option_bool( 'debug' )
		);

		// Iterate through each subscription (Form, Tag, Sequence), subscribing the Customer to each.
		foreach ( $subscriptions as $subscription_raw ) {
			list( $subscription['type'], $subscription['id'] ) = explode( ':', $subscription_raw );
			$this->subscribe_customer(
				$subscription['type'],
				$subscription['id'],
				$this->email( $order ),
				$this->first_name( $order ),
				$order_id
			);
		}

	}

	/**
	 * Subscribe the given email address to the Form, Tag or Sequence, adding an Order Note
	 * for each to the Order.
	 * 
	 * @since 	1.4.2
	 *
	 * @param 	string 	$resource_type 	Resource Type (form|tag|course).
	 * @param 	int 	$resource_id 	Resource ID (Form ID, Tag ID, Sequence ID).
	 * @param 	string 	$email 			Email Address.
	 * @param 	string 	$name 			Customer Name.
	 * @param 	int 	$order_id 		WooCommerce Order ID.
	 * @return 	mixed 					WP_Error | array
	 */
	public function subscribe_customer( $resource_type, $resource_id, $email, $name, $order_id ) {

		// Call API to subscribe the email address to the given Form, Tag or Sequence.
		switch ( $resource_type ) {
			case 'form':
				$result = $this->api->form_subscribe( $resource_id, $email, $name );
				break;

			case 'tag':
				$result = $this->api->tag_subscribe( $resource_id, $email );
				break;

			case 'sequence':
			case 'course':
				$result = $this->api->sequence_subscribe( $resource_id, $email );
				break;
		}

		// If an error occured, bail.
		if ( is_wp_error( $result ) ) {
			wc_create_order_note( 
				$order_id,
				$result->get_error_message()
			);
			return;
		}

		// Create an Order Note so that the Order shows the Customer was subscribed to a Form, Tag or Sequence.
		switch ( $resource_type ) {
			case 'form':
				wc_create_order_note( 
					$order_id,
                  	sprintf( 
                  		__( '[ConvertKit] Customer subscribed to the Form: %s', 'woocommerce-convertkit' ),
                  		$resource_id
                  	)
				);
				break;

			case 'tag':
				wc_create_order_note( 
					$order_id,
                  	sprintf( 
                  		__( '[ConvertKit] Customer subscribed to the Tag: %s', 'woocommerce-convertkit' ),
                  		$resource_id
                  	)
				);
				break;

			case 'sequence':
			case 'course':
				wc_create_order_note( 
					$order_id,
                  	sprintf( 
                  		__( '[ConvertKit] Customer subscribed to the Sequence: %s', 'woocommerce-convertkit' ),
                  		$resource_id
                  	)
				);
				break;
		}

		// Return result.
		return $result;

	}

	/**
	 * Send purchase data to ConvertKit for the given WooCommerce Order ID.
	 *
	 * @since 	1.4.2
	 * 
	 * @param 	int 	$order_id 	WooCommerce Order ID.
	 * @return 	mixed 				WP_Error | array
	 */
	public function send_purchase_data( $order_id ) {

		// Get WooCommerce Order.
		$order = wc_get_order( $order_id );

		// If no Order could be fetched, bail.
		if ( ! $order ) {
			return;
		}

		// If purchase data has already been sent to ConvertKit, bail
		// This ensures that we don't unecessarily send data multiple times
		// when the Order's status is transitioned.
		if ( $this->purchase_data_sent( $order_id ) ) {
			return;
		}
		
		// If customer isn't opting in, bail
		// We can't send purchase data if the customer hasn't opted in, because the ConvertKit API
		// will always subscribe the email address given in the purchase data.
		if ( ! $this->should_opt_in_customer( $order_id ) ) {
			return;
		}

		// Build array of Products for the API call.
		$products = array();
		foreach ( $order->get_items() as $item_key => $item ) {
			// If this Order Item's Product could not be found, skip it.
			if ( ! $item->get_product() ) {
				continue;
			}

			// Add Product to array of Products.
			$products[] = array(
				'pid'        => $item->get_product()->get_id(),
				'lid'        => $item_key,
				'name'       => $item->get_name(),
				'sku'        => $item->get_product()->get_sku(),
				'unit_price' => $item->get_product()->get_price(),
				'quantity'   => $item->get_quantity(),
			);
		}

		// Build API parameters.
		$purchase = array(
			'transaction_id'   => $order->get_order_number(),
			'email_address'    => $order->get_billing_email(),
			'first_name'       => $order->get_billing_first_name(),
			'currency'         => $order->get_currency(),
			'transaction_time' => $order->get_date_created()->date( 'Y-m-d H:i:s' ),
			'subtotal'         => round( floatval( $order->get_subtotal() ), 2 ),
			'tax'              => round( floatval( $order->get_total_tax( 'edit' ) ), 2 ),
			'shipping'         => round( floatval( $order->get_shipping_total( 'edit' ) ), 2 ),
			'discount'         => round( floatval( $order->get_discount_total( 'edit' ) ), 2 ),
			'total'            => round( floatval( $order->get_total( 'edit' ) ), 2 ),
			'status'           => 'paid',
			'products'         => $products,
			'integration'      => 'WooCommerce',
		);

		// Setup the API.
		$this->api = new CKWC_API( 
			$this->integration->get_option( 'api_key' ),
			$this->integration->get_option( 'api_secret' ),
			$this->integration->get_option_bool( 'debug' )
		);

		// Send purchase data to ConvertKit.
		$response = $this->api->purchase_create( $purchase );

		// If an error occured sending the purchase data to ConvertKit, add a WooCommerce Order note and bail.
		if ( is_wp_error( $response ) ) {
			$order->add_order_note( 
				sprintf(
					__( '[ConvertKit] Send Purchase Data Error: %1$s %2$s', 'woocommerce-convertkit' ),
					$response->get_error_code(),
					$response->get_error_message()
				)
			);

			return $response;
		}

		// Mark the purchase data as being sent, so future Order status transitions don't send it again.
		update_post_meta( $order_id, 'ckwc_purchase_data_sent', 'yes' );

		// Add a note to the WooCommerce Order that the purchase data sent successfully.
		$order->add_order_note( __( '[ConvertKit] Purchase Data sent successfully', 'woocommerce-convertkit' ) );

		// Return.
		return $response;
		
	}

	/**
	 * Determines if the given Order has had its purchase data sent to ConvertKit.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	int 	$order_id 	Order ID.
	 * @return 	bool 				Purchase Data successfully sent to ConvertKit
	 */
	private function purchase_data_sent( $order_id ) {

		if ( 'yes' === get_post_meta( $order_id, 'ckwc_purchase_data_sent', true ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Determines if the given Order has the opt in meta value set to 'yes',
	 * and that the 'Subscribe Customers' option is enabled in the Plugin settings.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	int 	$order_id 	Order ID.
	 * @return 	bool 				Customer can be opted in
	 */
	private function should_opt_in_customer( $order_id ) {

		// Get Post Meta value.
		$opt_in = get_post_meta( $order_id, 'ckwc_opt_in', true );

		// If opt in is anything other than 'yes', do not opt in.
		if ( $opt_in !== 'yes' ) {
			return false;
		}

		return true;

	}

	/**
	 * Returns the customer's email address for the given WooCommerce Order,
	 * immediately before it is sent to ConvertKit when subscribing the Customer
	 * to a Form or Tag.
	 * 
	 * @since 	1.0.0
	 * 
	 * @param 	WC_Order|WC_Order_Refund 	$order 	Order
	 * @return 	string 								Email Address 
	 */
	private function email( $order ) {

		// Get Email.
		$email = $order->get_billing_email();

		/**
		 * Returns the customer's email address for the given WooCommerce Order,
		 * immediately before it is sent to ConvertKit when subscribing the Customer
		 * to a Form or Tag.
		 * 
		 * @since 	1.0.0
		 * 
		 * @param 	string 						$email 	Email Address
		 * @param 	WC_Order|WC_Order_Refund 	$order 	Order
		 */
		$email = apply_filters( 'convertkit_for_woocommerce_email', $email, $order );

		// Return.
		return $email;

	}

	/**
	 * Returns the customer's first name for the given WooCommerce Order,
	 * immediately before it is sent to ConvertKit when subscribing the Customer
	 * to a Form or Tag.
	 * 
	 * @since 	1.0.0
	 * 
	 * @param 	WC_Order|WC_Order_Refund 	$order 	Order
	 * @return 	string 								Email Address 
	 */
	private function first_name( $order ) {

		// Get First Name.
		$first_name = $order->get_billing_first_name();

		/**
		 * Returns the customer's first name for the given WooCommerce Order,
		 * immediately before it is sent to ConvertKit when subscribing the Customer
		 * to a Form or Tag.
		 * 
		 * @since 	1.0.0
		 * 
		 * @param 	string 						$first_name 	First Name
		 * @param 	WC_Order|WC_Order_Refund 	$order 			Order
		 */
		$first_name = apply_filters( 'convertkit_for_woocommerce_first_name', $first_name, $order );

		// Return.
		return $first_name;

	}

}