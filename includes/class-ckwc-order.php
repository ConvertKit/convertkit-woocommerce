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
	 * Subscribe the customer's email address to a ConvertKit Form or Tag, if the Order's event
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
		if ( ! $this->requires_opt_in() ) {
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

		// Subscribe.
		$this->process_convertkit_subscriptions( 
			$subscriptions,
			$this->email( $order ),
			$this->first_name( $order ),
			$order_id
		);
		
	}

	/**
	 * For each subscription (sequence, tag, form) attached to a product,
	 * perform the relevant actions (subscribe & add order note)
	 *
	 * @param array $subscriptions
	 * @param string $email
	 * @param string $name
	 * @param string $order_id
	 */
	private function process_convertkit_subscriptions( $subscriptions, $email, $name, $order_id ) {

		foreach ( $subscriptions as $subscription_raw ) {
			list( $subscription['type'], $subscription['id'] ) = explode( ':', $subscription_raw );

			$subscription['function'] = "ckwc_convertkit_api_add_subscriber_to_{$subscription['type']}";

			$this->process_item_subscription( $subscription, $email, $name, $order_id );
		}

	}

	/**
	 * For each subscription (sequence, tag, form) attached to a product,
	 * perform the relevant actions (subscribe & add order note)
	 *
	 * @param array $subscription
	 * @param string $email
	 * @param string $name
	 * @param string $order_id
	 */
	private  function process_item_subscription( $subscription, $email, $name, $order_id ) {
	    // TODO add else{} block here to debug_log if function does not exist
		if ( function_exists( $subscription['function'] ) ) {
			$response = call_user_func( $subscription['function'], $subscription['id'], $email, $name );

			if ( ! is_wp_error( $response ) ) {
				$options = ckwc_get_subscription_options();
				$items   = array();
				foreach ( $options as $option ) {
					if ( $subscription['type'] !== $option['key'] ) {
						continue;
					}

					/**
					 * This ends up holding an array of the subscription items (tags, courses, or forms) our WP install knows about,
					 * which match the current subscription type, in `id => name` pairs
					 */
					// TODO should this be like `$items[] =`, so we don't stomp on the array each time through the loop?
					$items = $option['options'];
				}
				if ( $items ) {
					// we then check if the item ID we sent is in this array, and if so add an order note
					if ( isset( $items[ $subscription['id'] ] ) ) {
						switch ( $subscription['type'] ) {
							case 'tag':
							case 'form':
								wc_create_order_note( $order_id,
								                      sprintf( __( '[ConvertKit] Customer subscribed to the %s: %s',
								                                   'woocommerce-convertkit' ), $subscription['type'],
								                               $items[ $subscription['id'] ] ) );
								break;

							// Sequences are called "courses" for legacy reasons, so they get a special case
							case 'course':
								wc_create_order_note( $order_id,
								                      sprintf( __( '[ConvertKit] Customer subscribed to the %s: %s',
								                                   'woocommerce-convertkit' ), 'sequence',
								                               $items[ $subscription['id'] ] ) );
								break;
						}
					}
				}
			}

			if ( 'yes' === $this->get_option( 'debug' ) ) {
				$this->debug_log( 'API call: ' . $subscription['type'] . "\nResponse: \n" . print_r( $response,
				                                                                                     true ) );
			}
		}
	}

	/**
	 * Send purchase data to ConvertKit for the given WooCommerce Order ID.
	 *
	 * @since 	1.4.2
	 * 
	 * @param 	int 	$order_id 	WooCommerce Order ID.
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
		if ( ! $this->purchase_data_sent( $order_id ) ) {
			return;
		}
		
		// If customer isn't opting in, bail
		// We can't send purchase data if the customer hasn't opted in, because the ConvertKit API
		// will always subscribe the email address given in the purchase data.
		if ( ! $this->requires_opt_in( $order_id ) ) {
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
		$purchase_options = array(
			'api_secret' => $this->api_secret,
			'purchase'   => array(
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
			),
		);

		$this->debug_log( 'send payment request: ' . print_r( $purchase_options, true ) );

		// Send purchase data to ConvertKit.
		$response = ckwc_convertkit_api_request(
			'purchases',
			array(
				'api_key' => $this->api_key,
			),
			$purchase_options,
			array(
				'method' => 'POST'
			)
		);

		// If an error occured sending the purchase data to ConvertKit, add the error to the log and WooCommerce Order note.
		if ( is_wp_error( $response ) ) {
			$order->add_order_note( 'Send payment to ConvertKit error: ' . $response->get_error_code() . ' ' . $response->get_error_message(), 0, 'ConvertKit plugin' );
			$this->debug_log( 'Send payment response WP Error: ' . $response->get_error_code() . ' ' . $response->get_error_message() );
			return;
		}

		// Mark the purchase data as being sent, so future Order status transitions don't send it again.
		update_post_meta( $order_id, 'ckwc_purchase_data_sent', 'yes' );

		// Log the result and add a WooCommerce Order note.
		$order->add_order_note( 'Payment data sent to ConvertKit', 0, false );
		$this->debug_log( 'send payment response: ' . print_r( $response, true ) );
		
	}

	/**
	 * Write API request results to a debug log
	 * @param $message
	 */
	public function debug_log( $message ) {

		$debug = $this->get_option( 'debug' );
		if ( class_exists( 'WC_Logger' ) && ( 'yes' === $debug ) ) {
			$logger = new WC_Logger();
			$logger->add( 'convertkit', $message );
		}
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
	private function requires_opt_in( $order_id ) {

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