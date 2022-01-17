<?php
/**
 * ConvertKit Checkout class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Subscribes a WooCommerce Order's Customer to ConvertKit Forms, Tags and/or Sequences,
 * based on Product and Plugin settings.
 *
 * Sends Purchase Data to ConvertKit if enabled in the Plugin settings.
 *
 * @package CKWC
 * @author ConvertKit
 */
class CKWC_Order {

	/**
	 * Holds the WooCommerce Integration instance for this Plugin.
	 *
	 * @since   1.4.2
	 *
	 * @var     WC_Integration
	 */
	private $integration;

	/**
	 * Holds the ConvertKit API.
	 *
	 * @since   1.4.2
	 *
	 * @var     CKWC_API
	 */
	private $api;

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

		// Subscribe customer's email address to a form or tag.
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'maybe_subscribe_customer' ), 99999, 1 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'maybe_subscribe_customer' ), 99999, 3 );

		// Send Purchase Data.
		if ( $this->integration->get_option_bool( 'send_purchases' ) ) {
			add_action( 'woocommerce_order_status_changed', array( $this, 'send_purchase_data' ), 99999, 3 );
		}

	}

	/**
	 * Subscribe the customer's email address to a ConvertKit Form, Tag or Sequence, if the Order's event
	 * matches the Subscribe Event in this Plugin's Settings.
	 *
	 * @since   1.0.0
	 *
	 * @param   int    $order_id   WooCommerce Order ID.
	 * @param   string $status_old Order's Old Status.
	 * @param   string $status_new Order's New Status.
	 */
	public function maybe_subscribe_customer( $order_id, $status_old = 'new', $status_new = 'pending' ) {

		// Bail if the old and new status are the same i.e. the Order status did not change.
		if ( $status_old === $status_new ) {
			return;
		}

		// Bail if the Subscribe Event doesn't match the Order's new status.
		if ( $this->integration->get_option( 'event' ) !== $status_new ) {
			return;
		}

		// Bail if the Order does not require that we subscribe the Customer,
		// or the Customer was already subscribed.
		if ( ! $this->should_opt_in_customer( $order_id ) ) {
			return;
		}

		// Get WooCommerce Order.
		$order = wc_get_order( $order_id );

		// If no Order could be fetched, bail.
		if ( ! $order ) {
			return;
		}

		// Build an array of Forms, Tags and Sequences to subscribe the Customer to, based on
		// the global integration settings and any Product-specific settings.
		$subscriptions = array( $this->integration->get_option( 'subscription' ) );
		foreach ( $order->get_items() as $item ) {
			// Get the Form, Tag or Sequence for this Product.
			$resource_id = get_post_meta( $item['product_id'], 'ckwc_subscription', true );

			/**
			 * Define the Form, Tag or Sequence ID to subscribe the Customer to for the given Product.
			 * 
			 * @since 	1.4.2
			 * 
			 * @param 	mixed 	$resource_id 	Form, Tag or Sequence ID | empty string.
			 * @param   int    $order_id   		WooCommerce Order ID.
			 * @param   string $status_old 		Order's Old Status.
			 * @param   string $status_new 		Order's New Status.
			 */
			$resource_id = apply_filters( 'convertkit_for_woocommerce_order_maybe_subscribe_customer_resource_id', $resource_id, $order_id, $status_old, $status_new );

			// If no resource is specified for this Product, don't add it to the array.
			if ( empty( $resource_id ) ) {
				continue;
			}

			// Add to array of resources to subscribe the Customer to.
			$subscriptions[] = $resource_id;
		}

		// Remove any duplicate Forms, Tags and Sequences.
		$subscriptions = array_filter( array_unique( $subscriptions ) );

		/**
		 * Define the Forms, Tags and/or Sequences to subscribe the Customer to for this Order.
		 * 
		 * @since 	1.4.2
		 * 
		 * @param 	array  $subscriptions 	Subscriptions (array of Forms, Tags and/or Sequence IDs).
		 * @param   int    $order_id   		WooCommerce Order ID.
		 * @param   string $status_old 		Order's Old Status.
		 * @param   string $status_new 		Order's New Status.
		 */
		$subscriptions = apply_filters( 'convertkit_for_woocommerce_order_maybe_subscribe_customer_subscriptions', $subscriptions, $order_id, $status_old, $status_new );

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
				$this->name( $order ),
				$order_id
			);
		}

	}

	/**
	 * Subscribe the given email address to the Form, Tag or Sequence, adding an Order Note
	 * for each to the Order.
	 *
	 * @since   1.4.2
	 *
	 * @param   string $resource_type  Resource Type (form|tag|course).
	 * @param   int    $resource_id    Resource ID (Form ID, Tag ID, Sequence ID).
	 * @param   string $email          Email Address.
	 * @param   string $name           Customer Name.
	 * @param   int    $order_id       WooCommerce Order ID.
	 * @return  mixed                   WP_Error | array
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

		// Mark the Customer as being opted in, so future Order status transitions don't opt the Customer in a second time
		// e.g. if the Plugin subscribes the Customer on the 'processing' status, and the Order is then transitioned
		// from processing --> completed --> processing. 
		$this->mark_customer_opted_in( $order_id );

		// Create an Order Note so that the Order shows the Customer was subscribed to a Form, Tag or Sequence.
		switch ( $resource_type ) {
			case 'form':
				// Fetch Form from cached resources, so we can output the Form Name in the Order Note.
				$forms = new CKWC_Resource_Forms();
				$form = $forms->get_by_id( $resource_id );

				// Create Order Note.
				wc_create_order_note(
					$order_id,
					sprintf(
						/* translators: %1$s: Form Name, %2$s: Form ID */
						__( '[ConvertKit] Customer subscribed to the Form: %1$s [%2$s]', 'woocommerce-convertkit' ),
						( $form ? $form['name'] : '' ),
						$resource_id
					)
				);
				break;

			case 'tag':
				// Fetch Tag from cached resources, so we can output the Tag Name in the Order Note.
				$tags = new CKWC_Resource_Tags();
				$tag = $tags->get_by_id( $resource_id );

				// Create Order Note.
				wc_create_order_note(
					$order_id,
					sprintf(
						/* translators: %1$s: Tag Name, %2$s: Tag ID */
						__( '[ConvertKit] Customer subscribed to the Tag: %1$s [%2$s]', 'woocommerce-convertkit' ),
						( $tag ? $tag['name'] : '' ),
						$resource_id
					)
				);
				break;

			case 'sequence':
			case 'course':
				// Fetch Sequence from cached resources, so we can output the Sequence Name in the Order Note.
				$sequences = new CKWC_Resource_Sequences();
				$sequence = $sequences->get_by_id( $resource_id );

				// Create Order Note.
				wc_create_order_note(
					$order_id,
					sprintf(
						/* translators: %1$s: Sequence Name, %2$s: Sequence ID */
						__( '[ConvertKit] Customer subscribed to the Sequence: %1$s [%2$s]', 'woocommerce-convertkit' ),
						( $sequence ? $sequence['name'] : '' ),
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
	 * @since   1.4.2
	 *
	 * @param   int    $order_id   WooCommerce Order ID.
	 * @param   string $status_old Order's Old Status.
	 * @param   string $status_new Order's New Status.
	 * @return  mixed               WP_Error | array
	 */
	public function send_purchase_data( $order_id, $status_old = 'new', $status_new = 'pending' ) {

		// Get WooCommerce Order.
		$order = wc_get_order( $order_id );

		// If no Order could be fetched, bail.
		if ( ! $order ) {
			return;
		}

		// If purchase data has already been sent to ConvertKit, don't send any data to ConvertKit.
		// This ensures that we don't unecessarily send data multiple times
		// when the Order's status is transitioned.
		if ( $this->purchase_data_sent( $order_id ) ) {
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

		/**
		 * Define the data to send to the ConvertKit API to create a Purchase in ConvertKit
		 * https://developers.convertkit.com/#create-a-purchase
		 * 
		 * @since 	1.4.2
		 * 
		 * @param 	array  $purchase 		Purchase Data.
		 * @param   int    $order_id   		WooCommerce Order ID.
		 * @param   string $status_old 		Order's Old Status.
		 * @param   string $status_new 		Order's New Status.
		 */
		$purchase = apply_filters( 'convertkit_for_woocommerce_order_send_purchase_data', $purchase, $order_id, $status_old, $status_new );

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
					/* translators: %1$s: Error Code, %2$s: Error Message */
					__( '[ConvertKit] Send Purchase Data Error: %1$s %2$s', 'woocommerce-convertkit' ),
					$response->get_error_code(),
					$response->get_error_message()
				)
			);

			return $response;
		}

		// Mark the purchase data as being sent, so future Order status transitions don't send it again.
		$this->mark_purchase_data_sent( $order_id );

		// Add a note to the WooCommerce Order that the purchase data sent successfully.
		$order->add_order_note( __( '[ConvertKit] Purchase Data sent successfully', 'woocommerce-convertkit' ) );

		// Return.
		return $response;

	}

	/**
	 * Mark purchase data as being sent to ConvertKit for the given Order ID.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	int 	$order_id 	Order ID.
	 */
	private function mark_purchase_data_sent( $order_id ) {

		update_post_meta( $order_id, 'ckwc_purchase_data_sent', 'yes' );

	}

	/**
	 * Determines if the given Order has had its purchase data sent to ConvertKit.
	 *
	 * @since   1.4.2
	 *
	 * @param   int $order_id   Order ID.
	 * @return  bool                Purchase Data successfully sent to ConvertKit
	 */
	private function purchase_data_sent( $order_id ) {

		if ( 'yes' === get_post_meta( $order_id, 'ckwc_purchase_data_sent', true ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Mark the Order as having opted in to ConvertKit, so that subsequent status
	 * transitions can check whether the Customer was previously subscribed to
	 * ConvertKit.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param   int $order_id   Order ID.
	 */
	private function mark_customer_opted_in( $order_id ) {

		update_post_meta( $order_id, 'ckwc_opted_in', 'yes' );
		
	}

	/**
	 * Determines if the given Order has the opt in meta value set to 'yes',
	 * and that the Customer has not yet been subscribed to ConvertKit.
	 *
	 * @since   1.4.2
	 *
	 * @param   int $order_id   Order ID.
	 * @return  bool                Customer can be opted in
	 */
	private function should_opt_in_customer( $order_id ) {

		// If the Order already opted in the Customer, do not opt them in again.
		if ( 'yes' === get_post_meta( $order_id, 'ckwc_opted_in', true ) ) {
			return false;
		}

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
	 * @since   1.0.0
	 *
	 * @param   WC_Order|WC_Order_Refund $order  Order.
	 * @return  string                              Email Address
	 */
	private function email( $order ) {

		// Get Email.
		$email = $order->get_billing_email();

		/**
		 * Returns the customer's email address for the given WooCommerce Order,
		 * immediately before it is sent to ConvertKit when subscribing the Customer
		 * to a Form or Tag.
		 *
		 * @since   1.0.0
		 *
		 * @param   string                      $email  Email Address
		 * @param   WC_Order|WC_Order_Refund    $order  Order
		 */
		$email = apply_filters( 'convertkit_for_woocommerce_email', $email, $order );

		// Return.
		return $email;

	}

	/**
	 * Returns the customer's name for the given WooCommerce Order, based on the Plugin's
	 * Name Format setting (First Name, Last Name or First + Last Name),
	 * immediately before it is sent to ConvertKit when subscribing the Customer
	 * to a Form, Tag or Sequence.
	 *
	 * @since   1.0.0
	 *
	 * @param   WC_Order|WC_Order_Refund $order  Order.
	 * @return  string                              Email Address
	 */
	private function name( $order ) {

		switch ( $this->integration->get_option( 'name_format' ) ) {

			/**
			 * First Name
			 */
			case 'first':
				return $this->first_name( $order );
				break;

			/**
			 * Last Name
			 */
			case 'last':
				return $this->last_name( $order );
				break;

			/**
			 * First and Last Name
			 */
			default:
				return sprintf( "%s %s", $this->first_name( $order ), $this->last_name( $order ) );
				break;

		}

		// Get First Name.
		$first_name = $order->get_billing_first_name();

		/**
		 * Returns the customer's first name for the given WooCommerce Order,
		 * immediately before it is sent to ConvertKit when subscribing the Customer
		 * to a Form, Tag or Sequence.
		 *
		 * @since   1.0.0
		 *
		 * @param   string                      $first_name     First Name
		 * @param   WC_Order|WC_Order_Refund    $order          Order
		 */
		$first_name = apply_filters( 'convertkit_for_woocommerce_first_name', $first_name, $order );

		// Return.
		return $first_name;

	}

	/**
	 * Returns the customer's first name for the given WooCommerce Order,
	 * immediately before it is sent to ConvertKit when subscribing the Customer
	 * to a Form, Tag or Sequence.
	 *
	 * @since   1.0.0
	 *
	 * @param   WC_Order|WC_Order_Refund $order  Order.
	 * @return  string                              Email Address
	 */
	private function first_name( $order ) {

		// Get First Name.
		$first_name = $order->get_billing_first_name();

		/**
		 * Returns the customer's first name for the given WooCommerce Order,
		 * immediately before it is sent to ConvertKit when subscribing the Customer
		 * to a Form, Tag or Sequence.
		 *
		 * @since   1.0.0
		 *
		 * @param   string                      $first_name     First Name
		 * @param   WC_Order|WC_Order_Refund    $order          Order
		 */
		$first_name = apply_filters( 'convertkit_for_woocommerce_first_name', $first_name, $order );

		// Return.
		return $first_name;

	}

	/**
	 * Returns the customer's last name for the given WooCommerce Order,
	 * immediately before it is sent to ConvertKit when subscribing the Customer
	 * to a Form or Tag.
	 *
	 * @since   1.0.0
	 *
	 * @param   WC_Order|WC_Order_Refund $order  Order.
	 * @return  string                              Email Address
	 */
	private function last_name( $order ) {

		// Get Last Name.
		$last_name = $order->get_billing_last_name();

		/**
		 * Returns the customer's last name for the given WooCommerce Order,
		 * immediately before it is sent to ConvertKit when subscribing the Customer
		 * to a Form or Tag.
		 *
		 * @since   1.0.0
		 *
		 * @param   string                      $last_name     	Last Name
		 * @param   WC_Order|WC_Order_Refund    $order          Order
		 */
		$last_name = apply_filters( 'convertkit_for_woocommerce_last_name', $last_name, $order );

		// Return.
		return $last_name;

	}

}
