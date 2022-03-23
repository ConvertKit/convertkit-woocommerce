<?php
/**
 * ConvertKit Order class.
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
	 * @var     CKWC_Integration
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
	 * The Meta Key used to store that the WooCommerce Order
	 * was successfully sent to ConvertKit.
	 *
	 * @since   1.4.4
	 *
	 * @var     string
	 */
	private $purchase_data_sent_meta_key = 'ckwc_purchase_data_sent';

	/**
	 * The Meta Key used to store the ConvertKit Transaction ID
	 * when purchase data is successfully sent to ConvertKit
	 * for a WooCommerce Order.
	 *
	 * @since   1.4.4
	 *
	 * @var     string
	 */
	private $purchase_data_id_meta_key = 'ckwc_purchase_data_id';

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

		// Subscribe customer's email address to a form, tag or sequence.
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

		// If configured in the Integration, map Phone, Billing and/or Shipping Addresses to ConvertKit
		// Custom Fields now.
		$fields = $this->custom_field_data( $order );

		// Build an array of Forms, Tags and Sequences to subscribe the Customer to, based on
		// the global integration settings and any Product-specific settings.
		$subscriptions = array( $this->integration->get_option( 'subscription' ) );
		foreach ( $order->get_items() as $item ) {
			// Get the Form, Tag or Sequence for this Product.
			$resource_id = get_post_meta( $item['product_id'], 'ckwc_subscription', true );

			/**
			 * Define the Form, Tag or Sequence ID to subscribe the Customer to for the given Product.
			 *
			 * @since   1.4.2
			 *
			 * @param   mixed   $resource_id    Form, Tag or Sequence ID | empty string.
			 * @param   int    $order_id        WooCommerce Order ID.
			 * @param   string $status_old      Order's Old Status.
			 * @param   string $status_new      Order's New Status.
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
		 * @since   1.4.2
		 *
		 * @param   array  $subscriptions   Subscriptions (array of Forms, Tags and/or Sequence IDs).
		 * @param   int    $order_id        WooCommerce Order ID.
		 * @param   string $status_old      Order's Old Status.
		 * @param   string $status_new      Order's New Status.
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
				(int) $subscription['id'],
				$this->email( $order ),
				$this->name( $order ),
				$order_id,
				$fields
			);
		}

		// The customer was subscribed to ConvertKit, so request a Plugin review.
		// This can safely be called multiple times, as the review request
		// class will ensure once a review request is dismissed by the user,
		// it is never displayed again.
		WP_CKWC()->get_class( 'review_request' )->request_review();

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
	 * @param   mixed  $custom_fields  Custom Fields (false | array).
	 * @return  mixed                   WP_Error | array
	 */
	public function subscribe_customer( $resource_type, $resource_id, $email, $name, $order_id, $custom_fields ) {

		// Call API to subscribe the email address to the given Form, Tag or Sequence.
		switch ( $resource_type ) {
			case 'form':
				$result = $this->api->form_subscribe( $resource_id, $email, $name, $custom_fields );
				break;

			case 'tag':
				$result = $this->api->tag_subscribe( $resource_id, $email, $name, $custom_fields );
				break;

			case 'sequence':
			case 'course':
				$result = $this->api->sequence_subscribe( $resource_id, $email, $name, $custom_fields );
				break;

			default:
				$result = new WP_Error(
					'convertkit_for_woocommerce_order_subscribe_customer_error',
					sprintf(
						/* translators: Resource Type */
						__( 'Resource type %s is not supported in CKWC_Order::subscribe_customer()', 'woocommerce-convertkit' ),
						$resource_type
					)
				);
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
				$form  = $forms->get_by_id( $resource_id );

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
				$tag  = $tags->get_by_id( $resource_id );

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
				$sequence  = $sequences->get_by_id( $resource_id );

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
	 * @return  mixed               false | WP_Error | array
	 */
	public function send_purchase_data( $order_id, $status_old = 'new', $status_new = 'pending' ) {

		// Bail if the old and new status are the same i.e. the Order status did not change.
		if ( $status_old === $status_new ) {
			return false;
		}

		// Bail if the Purchase Data Event doesn't match the Order's status.
		if ( $this->integration->get_option( 'send_purchases_event' ) !== $status_new ) {
			return false;
		}

		// Get WooCommerce Order.
		$order = wc_get_order( $order_id );

		// If no Order could be fetched, bail.
		if ( ! $order ) {
			return new WP_Error(
				'convertkit_for_woocommerce_error_order_missing',
				sprintf(
					/* translators: WooCommerce Order ID */
					__( 'Order ID %s could not be found in WooCommerce.', 'woocommerce-convertkit' ),
					$order_id
				)
			);
		}

		// If purchase data has already been sent to ConvertKit, don't send any data to ConvertKit.
		// This ensures that we don't unecessarily send data multiple times
		// when the Order's status is transitioned.
		if ( $this->purchase_data_sent( $order_id ) ) {
			return new WP_Error(
				'convertkit_for_woocommerce_error_order_exists',
				sprintf(
					/* translators: WooCommerce Order ID */
					__( 'Order ID %s has already been sent to ConvertKit.', 'woocommerce-convertkit' ),
					$order_id
				)
			);
		}

		// Build array of Products for the API call.
		$products = array();
		foreach ( $order->get_items() as $item_key => $item ) {
			// If this Order Item's Product could not be found, skip it.
			if ( ! $item->get_product() ) { // @phpstan-ignore-line
				continue;
			}

			// Add Product to array of Products.
			$products[] = array(
				'pid'        => $item->get_product()->get_id(), // @phpstan-ignore-line
				'lid'        => $item_key,
				'name'       => $item->get_name(),
				'sku'        => $item->get_product()->get_sku(), // @phpstan-ignore-line
				'unit_price' => $item->get_product()->get_price(), // @phpstan-ignore-line
				'quantity'   => $item->get_quantity(),
			);
		}

		// If no Products exist, mark purchase data as sent and return.
		if ( ! count( $products ) ) {
			// Mark the purchase data as being sent, so future Order status transitions don't send it again.
			$this->mark_purchase_data_sent( $order_id, 0 );

			// Add a note to the WooCommerce Order that no purchase data was sent.
			$order->add_order_note( __( '[ConvertKit] Purchase Data skipped, as this Order has no Products', 'woocommerce-convertkit' ) );

			// Return.
			return true;
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
		 * @since   1.4.2
		 *
		 * @param   array  $purchase        Purchase Data.
		 * @param   int    $order_id        WooCommerce Order ID.
		 * @param   string $status_old      Order's Old Status.
		 * @param   string $status_new      Order's New Status.
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
		$this->mark_purchase_data_sent( $order_id, $response['id'] );

		// Add a note to the WooCommerce Order that the purchase data sent successfully.
		$order->add_order_note( __( '[ConvertKit] Purchase Data sent successfully', 'woocommerce-convertkit' ) );

		// The customer's purchase data was sent to ConvertKit, so request a Plugin review.
		// This can safely be called multiple times, as the review request
		// class will ensure once a review request is dismissed by the user,
		// it is never displayed again.
		WP_CKWC()->get_class( 'review_request' )->request_review();

		// Return.
		return $response;

	}

	/**
	 * Returns an array of WooCommerce Orders that have not had their Purchase Data
	 * sent to ConvertKit.
	 *
	 * @since   1.4.3
	 *
	 * @return  mixed   false | array
	 */
	public function get_orders_not_sent_to_convertkit() {

		// Depending on the Purchase Data Event setting, determine the Order statuses that should
		// be included.
		switch ( $this->integration->get_option( 'send_purchases_event' ) ) {
			case 'completed':
				// Only past Orders marked as completed should be included when syncing past Orders.
				// This ensures we don't include Orders created since the Plugin's activation that
				// are marked as Processing and should only be sent once marked as Completed.
				$post_statuses = array(
					'wc-completed',
				);
				break;

			case 'processing':
			default:
				// Any past Orders that are marked as processing or completed should be included when syncing
				// past Orders.
				$post_statuses = array(
					'wc-processing',
					'wc-completed',
				);
				break;
		}

		// Run query to fetch Order IDs whose Purchase Data has not been sent to ConvertKit.
		$query = new WP_Query(
			array(
				'post_type'              => 'shop_order',
				'posts_per_page'         => -1,

				// Only include Orders that do not match the Purchase Data Event integration setting.
				'post_status'            => $post_statuses,

				// Only include Orders that do not have a ConvertKit Purchase Data ID.
				'meta_query'             => array(
					array(
						'key'     => $this->purchase_data_id_meta_key,
						'compare' => 'NOT EXISTS',
					),
				),

				// For performance, don't update caches and just return Order IDs, not complete objects.
				'fields'                 => 'ids',
				'cache_results'          => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		// If no Orders exist that have not had their Purchase Data sent to ConvertKit,
		// return false.
		if ( ! $query->post_count ) {
			return false;
		}

		// Return the array of Order IDs.
		return $query->posts;

	}

	/**
	 * Mark purchase data as being sent to ConvertKit for the given Order ID.
	 *
	 * @since   1.4.2
	 *
	 * @param   int $order_id                       Order ID.
	 * @param   int $convertkit_purchase_data_id    ConvertKit Purchase ID (different from the WooCommerce Order ID, and set by ConvertKit).
	 */
	private function mark_purchase_data_sent( $order_id, $convertkit_purchase_data_id ) {

		update_post_meta( $order_id, $this->purchase_data_sent_meta_key, 'yes' );
		update_post_meta( $order_id, $this->purchase_data_id_meta_key, $convertkit_purchase_data_id );

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

		// Return false if Purchase Data Sent Meta Key isn't yes.
		if ( 'yes' !== get_post_meta( $order_id, $this->purchase_data_sent_meta_key, true ) ) {
			return false;
		}

		// Return false if Purchase Data ID Meta Key doesn't exist in this Order.
		// This is stored in 1.4.3 and higher, ensuring we have a mapping
		// stored for the WooCommerce Order ID --> ConvertKit Purchase / Transaction ID.
		if ( ! metadata_exists( 'post', $order_id, $this->purchase_data_id_meta_key ) ) {
			return false;
		}

		// Purchase data for this Order has previously been sent to ConvertKit.
		return true;

	}

	/**
	 * Mark the Order as having opted in to ConvertKit, so that subsequent status
	 * transitions can check whether the Customer was previously subscribed to
	 * ConvertKit.
	 *
	 * @since   1.4.2
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

		// If opt in is anything other than 'yes', do not opt in.
		if ( 'yes' !== get_post_meta( $order_id, 'ckwc_opt_in', true ) ) {
			return false;
		}

		// If here, permit opt in.
		$should_opt_in_customer = true;

		/**
		 * Determine if the Customer should be opted in to ConvertKit.
		 * If the Order already opted in the Customer, this filter will not be fired.
		 * If the Order does not permit the Customer be opted in (i.e. they declined at checkout),
		 * this filter will not be fired.
		 *
		 * @since   1.4.4
		 *
		 * @param   bool    $should_opt_in_customer     Should opt in Customer.
		 * @param   int     $order_id                   Order ID.
		 */
		$should_opt_in_customer = apply_filters( 'convertkit_for_woocommerce_order_should_opt_in_customer', $should_opt_in_customer, $order_id );

		// Return.
		return $should_opt_in_customer;

	}

	/**
	 * Returns the customer's email address for the given WooCommerce Order,
	 * immediately before it is sent to ConvertKit when subscribing the Customer
	 * to a Form, Tag or Sequence.
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
		 * to a Form, Tag or Sequence.
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
				$name = $this->first_name( $order );
				break;

			/**
			 * Last Name
			 */
			case 'last':
				$name = $this->last_name( $order );
				break;

			/**
			 * First and Last Name
			 */
			default:
				$name = sprintf( '%s %s', $this->first_name( $order ), $this->last_name( $order ) );
				break;

		}

		/**
		 * Returns the customer's name for the given WooCommerce Order,
		 * immediately before it is sent to ConvertKit when subscribing the Customer
		 * to a Form, Tag or Sequence.
		 *
		 * @since   1.0.0
		 *
		 * @param   string                      $name   Name
		 * @param   WC_Order|WC_Order_Refund    $order  Order
		 */
		$name = apply_filters( 'convertkit_for_woocommerce_order_name', $name, $order );

		// Return.
		return $name;

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
	 * to a Form, Tag or Sequence.
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
		 * to a Form, Tag or Sequence.
		 *
		 * @since   1.0.0
		 *
		 * @param   string                      $last_name      Last Name
		 * @param   WC_Order|WC_Order_Refund    $order          Order
		 */
		$last_name = apply_filters( 'convertkit_for_woocommerce_last_name', $last_name, $order );

		// Return.
		return $last_name;

	}

	/**
	 * Returns an array of ConvertKit Custom Field Key/Value pairs, with values
	 * comprising of Order data based, to be sent to ConvertKit when an Order's
	 * Customer is subscribed via a Form, Tag or Sequence.
	 *
	 * Returns false if no Order data should be stored in ConvertKit Custom Fields.
	 *
	 * @since   1.4.3
	 *
	 * @param   WC_Order|WC_Order_Refund $order  Order.
	 * @return  mixed                            array | false
	 */
	private function custom_field_data( $order ) {

		$fields = array();

		if ( $this->integration->get_option( 'custom_field_phone' ) ) {
			$fields[ $this->integration->get_option( 'custom_field_phone' ) ] = $order->get_billing_phone();
		}
		if ( $this->integration->get_option( 'custom_field_billing_address' ) ) {
			$fields[ $this->integration->get_option( 'custom_field_billing_address' ) ] = str_replace( '<br/>', ', ', $order->get_formatted_billing_address() );
		}
		if ( $this->integration->get_option( 'custom_field_shipping_address' ) ) {
			$fields[ $this->integration->get_option( 'custom_field_shipping_address' ) ] = str_replace( '<br/>', ', ', $order->get_formatted_shipping_address() );
		}
		if ( $this->integration->get_option( 'custom_field_payment_method' ) ) {
			$fields[ $this->integration->get_option( 'custom_field_payment_method' ) ] = $order->get_payment_method();
		}
		if ( $this->integration->get_option( 'custom_field_customer_note' ) ) {
			$fields[ $this->integration->get_option( 'custom_field_customer_note' ) ] = $order->get_customer_note();
		}

		/**
		 * Returns an array of ConvertKit Custom Field Key/Value pairs, with values
		 * comprising of Order data based, to be sent to ConvertKit when an Order's
		 * Customer is subscribed via a Form, Tag or Sequence.
		 *
		 * Returns false if no Order data should be stored in ConvertKit Custom Fields.
		 *
		 * @since   1.4.3
		 *
		 * @param   mixed                       $fields     Custom Field Key/Value pairs (false | array).
		 * @param   WC_Order|WC_Order_Refund    $order      WooCommerce Order.
		 */
		$fields = apply_filters( 'convertkit_for_woocommerce_custom_field_data', $fields, $order );

		// If the fields array is empty, no Custom Field mappings exist.
		if ( ! count( $fields ) ) {
			return false;
		}

		return $fields;

	}

}
