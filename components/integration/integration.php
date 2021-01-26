<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CKWC_Integration
 */
class CKWC_Integration extends WC_Integration {

	/**
     * @var string
     */
	public $api_key;

	/**
     * @var string
     */
	private $api_secret;

	/**
     * @var string
     */
	private $subscription;

	/**
     * @var string
     */
	public $enabled;

	/**
     * @var string
     */
	private $event;

	/**
     * @var string
     */
	private $send_purchases;

	/**
	 * @var string
	 */
	private $send_manual_purchases;

	/**
     * @var string
     */
	private $display_opt_in;

	/**
     * @var string
     */
	private $opt_in_label;

	/**
     * @var string
     */
	private $opt_in_status;

	/**
     * @var string
     */
	private $opt_in_location;

	/**
     * @var string
     */
	private $name_format;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'ckwc';
		$this->method_title       = __( 'ConvertKit', 'woocommerce-convertkit' );
		$this->method_description = __( 'Enter your ConvertKit settings below to control how WooCommerce integrates with your ConvertKit account.', 'woocommerce-convertkit' );

		// Initialize form fields
		$this->init_form_fields();

		// Initialize settings
		$this->init_settings();

		// API interaction
		$this->api_key      = $this->get_option( 'api_key' );
		$this->api_secret   = $this->get_option( 'api_secret' );
		$this->subscription = $this->get_option( 'subscription' );

		// Enabled and when it should take place
		$this->enabled               = $this->get_option( 'enabled' );
		$this->event                 = $this->get_option( 'event' );
		$this->send_purchases        = $this->get_option( 'send_purchases' );
		$this->send_manual_purchases = $this->get_option( 'send_manual_purchases' );

		// Opt-in field
		$this->display_opt_in  = $this->get_option( 'display_opt_in' );
		$this->opt_in_label    = $this->get_option( 'opt_in_label' );
		$this->opt_in_status   = $this->get_option( 'opt_in_status' );
		$this->opt_in_location = $this->get_option( 'opt_in_location' );
		$this->name_format     = $this->get_option( 'name_format' );

		if ( is_admin() ) {
			add_filter( 'plugin_action_links_' . CKWC_PLUGIN_BASENAME, array( $this, 'plugin_links' ) );

			add_action( "woocommerce_update_options_integration_{$this->id}", array( $this, 'process_admin_options' ) );

			add_filter( "woocommerce_settings_api_sanitized_fields_{$this->id}", array( $this, 'sanitize_settings' ) );

			add_action( 'add_meta_boxes_product', array( $this, 'add_meta_boxes' ) );
			add_action( 'save_post_product', array( $this, 'save_product' ) );


			add_action( 'wp_ajax_ckwc_refresh_subscription_options', array( $this, 'refresh_subscription_options' ) );
		}

		if ( 'yes' === $this->enabled && 'yes' === $this->display_opt_in ) {
			add_filter( 'woocommerce_checkout_fields', array( $this, 'add_opt_in_checkbox' ) );
		}

		if ( 'yes' === $this->enabled ) {
			add_action( 'woocommerce_checkout_update_order_meta',  array( $this, 'save_opt_in_checkbox' ) );

			add_action( 'woocommerce_checkout_update_order_meta',  array( $this, 'order_status' ), 99999, 1 );
			add_action( 'woocommerce_order_status_changed',        array( $this, 'order_status' ), 99999, 3 );

			if ( 'yes' === $this->send_purchases ){
				add_action( 'woocommerce_order_status_changed',    array( $this, 'send_payment' ), 99999, 4 );
				add_action( 'woocommerce_order_status_changed',    array( $this, 'handle_cod_or_check_order_completion' ), 99999, 4 );
			}
		}

	}

	/**
	 * @param $post
	 */
	public function add_meta_boxes( $post ) {
		add_meta_box( 'ckwc', __( 'ConvertKit Integration', 'woocommerce-convertkit' ), array( $this, 'display_meta_box' ), null, 'side', 'default' );
	}

	/**
	 * @param $post
	 */
	public function display_meta_box( $post ) {
		$subscription = get_post_meta( $post->ID, 'ckwc_subscription', true );
		$options      = empty( $this->api_key ) ? false : ckwc_get_subscription_options();

		include( 'views/meta-box.php' );
	}

	/**
	 * @param $post_id
	 */
	public function save_product( $post_id ) {
		$data = stripslashes_deep( $_POST ); // WPCS: input var okay. CSRF ok.

		if ( isset( $data['ckwc_nonce'] ) && wp_verify_nonce( $data['ckwc_nonce'], 'ckwc' ) && isset( $data['ckwc_subscription'] ) ) {
			update_post_meta( $post_id, 'ckwc_subscription', $data['ckwc_subscription'] );
		}
	}

	/**
	 *
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'       => __( 'Enable/Disable', 'woocommerce-convertkit' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable ConvertKit integration', 'woocommerce-convertkit' ),
				'default'     => 'no',
			),

			'event' => array(
				'title'       => __( 'Subscribe Event', 'woocommerce-convertkit' ),
				'type'        => 'select',
				'default'     => 'pending',
				'description' => __( 'When should customers be subscribed?', 'woocommerce-convertkit' ),
				'desc_tip'    => false,
				'options'     => array(
					'pending'    => __( 'Order Created', 'woocommerce-convertkit' ),
					'processing' => __( 'Order Processing', 'woocommerce-convertkit' ),
					'completed'  => __( 'Order Completed', 'woocommerce-convertkit' ),
				),
			),

			'display_opt_in' => array(
				'title'       => __( 'Display Opt-In Checkbox', 'woocommerce-convertkit' ),
				'label'       => __( 'Display an Opt-In checkbox on checkout', 'woocommerce-convertkit' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __( 'If enabled, customers will only be subscribed if the "Opt-In" checkbox presented on checkout is checked.', 'woocommerce-convertkit' ),
				'desc_tip'    => false,
			),

			'opt_in_label' => array(
				'title'       => __( 'Opt-In Checkbox Label', 'woocommerce-convertkit' ),
				'type'        => 'text',
				'default'     => __( 'I want to subscribe to the newsletter', 'woocommerce-convertkit' ),
				'description' => __( 'Optional (only used if the above field is checked): Customize the label next to the opt-in checkbox.', 'woocommerce-convertkit' ),
				'desc_tip'    => false,
			),

			'opt_in_status' => array(
				'title'       => __( 'Opt-In Checkbox<br />Default Status', 'woocommerce-convertkit' ),
				'type'        => 'select',
				'default'     => 'checked',
				'description' => __( 'The default state of the opt-in checkbox', 'woocommerce-convertkit' ),
				'desc_tip'    => false,
				'options'     => array(
					'checked'   => __( 'Checked', 'woocommerce-convertkit' ),
					'unchecked' => __( 'Unchecked', 'woocommerce-convertkit' ),
				),
			),

			'opt_in_location' => array(
				'title'       => __( 'Opt-In Checkbox<br />Display Location', 'woocommerce-convertkit' ),
				'type'        => 'select',
				'default'     => 'billing',
				'description' => __( 'Where to display the opt-in checkbox on the checkout page (under Billing Info or Order Info).', 'woocommerce-convertkit' ),
				'desc_tip'    => false,
				'options'     => array(
					'billing' => __( 'Billing', 'woocommerce-convertkit' ),
					'order'   => __( 'Order', 'woocommerce-convertkit' ),
				),
			),

			'api_key' => array(
				'title'       => __( 'API Key', 'woocommerce-convertkit' ),
				'type'        => 'text',
				'default'     => '',
				// translators: this is a url to the ConvertKit site.
				'description' => sprintf( __( 'If you already have an account, <a href="%1$s" target="_blank">click here to retrieve your API Key</a>.<br />If you don\'t have a ConvertKit account, you can <a href="%2$s" target="_blank">sign up for one here</a>.', 'woocommerce-convertkit' ), esc_attr( esc_html( 'https://app.convertkit.com/account/edit' ) ), esc_attr( esc_url( 'http://convertkit.com/pricing/' ) ) ),
				'desc_tip'    => false,
			),

			'api_secret' => array(
				'title'       => __( 'API Secret', 'woocommerce-convertkit' ),
				'type'        => 'text',
				'default'     => '',
				// translators: this is a url to the ConvertKit site.
				'description' => sprintf( __( 'If you already have an account, <a href="%1$s" target="_blank">click here to retrieve your API Secret</a>.<br />If you don\'t have a ConvertKit account, you can <a href="%2$s" target="_blank">sign up for one here</a>', 'woocommerce-convertkit.' ), esc_attr( esc_html( 'https://app.convertkit.com/account/edit' ) ), esc_attr( esc_url( 'http://convertkit.com/pricing/' ) ) ),
				'desc_tip'    => false,
			),

			'subscription' => array(
				'title'       => __( 'Subscription', 'woocommerce-convertkit' ),
				'type'        => 'subscription',
				'default'     => '',
				'description' => __( 'Customers will be added to the selected item', 'woocommerce-convertkit' ),
			),

			'refresh_forms' => array(
				'title'       => __( 'Refresh forms', 'woocommerce-convertkit' ),
				'type'        => 'refresh',
				'default'     => '',
				'description' => __( 'Refresh forms', 'woocommerce-convertkit' ),
			),

			'name_format' => array(
				'title'       => __( 'Name Format', 'woocommerce-convertkit' ),
				'type'        => 'select',
				'default'     => 'first',
				'description' => __( 'How should the customer name be sent to ConvertKit?', 'woocommerce-convertkit' ),
				'desc_tip'    => false,
				'options'     => array(
					'first'   => __( 'Billing First Name', 'woocommerce-convertkit' ),
					'last'    => __( 'Billing Last Name', 'woocommerce-convertkit' ),
					'both'    => __( 'Billing First Name + Billing Last Name', 'woocommerce-convertkit' ),
				),
			),

			'send_purchases' => array(
				'title'       => __( 'Purchases', 'woocommerce-convertkit' ),
				'label'       => __( 'Send purchase data to ConvertKit.', 'woocommerce-convertkit' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __( '', 'woocommerce-convertkit' ),
				'desc_tip'    => false,
			),

			'send_manual_purchases' => array(
				'label'       => __( 'Send purchase data from manual orders to ConvertKit.', 'woocommerce-convertkit' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __( 'Purchase data from orders created manually in the admin area will be sent to ConvertKit.', 'woocommerce-convertkit' ),
				'desc_tip'    => false,
			),

			'debug' => array(
				'title'       => __( 'Debug', 'woocommerce-convertkit' ),
				'type'        => 'checkbox',
				'label'       => __('Write data to a log file', 'woocommerce-convertkit'),
				'description' => __( 'You can view the log file by going to WooCommerce > Status, click the Logs tab, then selecting convertkit.', 'woocommerce-convertkit' ),
				'default'     => 'no',
			),
		);

		ob_start();
		include( 'resources/integration.js' );
		$code = ob_get_clean();

		wc_enqueue_js( $code );
	}

	/**
	 * @param $key
	 * @param $data
	 *
	 * @return string
	 */
	public function generate_subscription_html( $key, $data ) {
		$field    = $this->get_field_key( $key );
		$defaults = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
			'options'           => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		$options = false;

		if ( ! empty( $this->api_key ) ) {
			$options = ckwc_get_subscription_options();
		}

		ob_start();

		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">
				<?php if ( $options ) { ?>
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<select class="select <?php echo esc_attr( $data['class'] ); ?>" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?>>
						<option <?php selected( '', $this->get_option( $key ) ); ?> value=""><?php _e( 'Select a subscription option...', 'woocommerce-convertkit' ); ?></option>
						<?php foreach ( $options as $option_group ) {
							if ( empty( $option_group['options'] ) ) {
								continue;
							} ?>
						<optgroup label="<?php echo esc_attr( $option_group['name'] ); ?>">
							<?php foreach ( $option_group['options'] as $id => $name ) {
								$value = "{$option_group['key']}:{$id}"; ?>
							<option <?php selected( $value, $this->get_option( $key ) ); ?> value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $name ); ?></option>
							<?php } ?>
						</optgroup>
						<?php } ?>
					</select>
					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
				<?php } else { ?>
				<p class="description"><?php _e( 'Please provide a valid ConvertKit API Key.', 'woocommerce-convertkit' ); ?></p>
				<?php } ?>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	/**
     * Generates the HTML for the "Refresh subscription options" button on the settings page
     *
	 * @param $key
	 * @param $data
	 *
	 * @return string
	 */
	public function generate_refresh_html( $key, $data ) {
		$field    = $this->get_field_key( $key );
		$defaults = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
			'options'           => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		$has_api = isset( $this->api_key ) ? esc_attr( $this->api_key ) : false;

		$html = '<input ' . ( $has_api ? '' : 'style="display:none;"' ) . ' type="submit" name="refresh" id="refresh_ckwc_subscription_options" class="button" value="' . __( 'Refresh subscription options', 'convertkit' ) . '"><span id="refreshCKSpinner" class="spinner"></span>';

	    ob_start();
	    ?>
        <tr valign="top">
        <th scope="row" class="titledesc">
            <label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
	        <?php echo $this->get_tooltip_html( $data ); ?>
        </th>
        <td class="forminp">
            <?php echo $html; ?>
        </td>
        <?php

        return ob_get_clean();
	}

	/**
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function sanitize_settings( $settings ) {
		$settings['api_key'] = trim( $settings['api_key'] );

		return $settings;
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function validate_api_key_field( $key ) {
		$field = $this->get_field_key( $key );
		$value = $_POST[ $field ]; // WPCS: CSRF ok.

		if ( empty( $value ) ) {
			$this->errors[ $key ] = __( 'Please provide your ConvertKit API Key.', 'woocommerce-convertkit' );
		} else {
			$forms = ckwc_convertkit_api_get_forms( $value );

			if ( is_wp_error( $forms ) ) {
				$this->errors[ $key ] = __( 'Your ConvertKit API Key appears to be invalid. Please double check the value.', 'woocommerce-convertkit' );
			}
		}

		return $value;
	}

	/**
	 *
	 */
	public function display_errors() {
		if ( ! empty( $this->errors ) ) {
			foreach ( $this->errors as $key => $value ) {
				printf( '<div class="error" id="ckwc_error_%s"><p>%s</p></div>', esc_attr( $key ), esc_html( $value ) );
			}
		}
	}

	/**
	 * @param $fields
	 *
	 * @return mixed
	 */
	public function add_opt_in_checkbox( $fields ) {
		$section = 'billing' === $this->opt_in_location ? 'billing' : 'order';

		$fields[ $section ]['ckwc_opt_in'] = array(
			'type'    => 'checkbox',
			'label'   => $this->opt_in_label,
			'default' => 'checked' === $this->opt_in_status,
		);

		return $fields;
	}

	/**
	 * @param $order_id
	 */
	public function save_opt_in_checkbox( $order_id ) {
		$opt_in = ('no' === $this->display_opt_in || isset( $_POST['ckwc_opt_in'] )) ? 'yes' : 'no'; // WPCS: CSRF ok.

		update_post_meta( $order_id, 'ckwc_opt_in', $opt_in );
	}

	/**
	 * @param int $order_id
	 * @param string $status_old
	 * @param string $status_new
	 */
	public function order_status( $order_id, $status_old = 'new', $status_new = 'pending' ) {

		$api_key_correct = ! empty( $this->api_key );
		$status_correct  = $status_new === $this->event;
		$opt_in_correct  = 'yes' === get_post_meta( $order_id, 'ckwc_opt_in', 'no' );

		if ( $api_key_correct && $status_correct && $opt_in_correct ) {
			$order = wc_get_order( $order_id );
			$items = $order->get_items();
			$email = $this->email( $order );
			$name  = $this->name_format( $this->first_name( $order ), $this->last_name( $order ) );

			/**
			 * $subscriptions is an array of type:id pairs, e.g. tag:123456
			 *
			 * First we fill it with the global one in the WooCommerce Integration settings
			 */
			$subscriptions = array( $this->subscription );

			/**
			 * Then we add any product-specific subscriptions for items in this order
			 */
			foreach ( $items as $item ) {
				$subscriptions[] = get_post_meta( $item['product_id'], 'ckwc_subscription', true );
			}

			/**
			 * Then we keep only unique elements
			 */
			$subscriptions = array_filter( array_unique( $subscriptions ) );
			$this->process_convertkit_subscriptions( $subscriptions, $email, $name, $order_id );
		}
	}

	/**
	 * @param int $order_id
	 * @param string $status_old
	 * @param string $status_new
	 * @param WC_Order $order
	 */
	public function handle_cod_or_check_order_completion( $order_id, $status_old, $status_new, $order ) {

		$api_key_correct = ! empty( $this->api_key );
		$correct_status = $status_new === $this->event;;
		$payment_methods = array( 'cod', 'cheque', 'check' );

		if ( 'yes' === $this->send_manual_purchases ){
		    $payment_methods[] = '';
		}

		if ( $api_key_correct && $correct_status && in_array( $order->get_payment_method( null ), $payment_methods ) ) {

			$products = array();

			foreach( $order->get_items( ) as $item_key => $item ) {
				if ( ! $item->get_product() ) {
					continue;
				}
				$products[] = array(
					'pid'        => $item->get_product()->get_id(),
					'lid'        => $item_key,
					'name'       => $item->get_name(),
					'sku'        => $item->get_product()->get_sku(),
					'unit_price' => $item->get_product()->get_price(),
					'quantity'   => $item->get_quantity(),
				);
			}

			$purchase_options = array(
				'api_secret' => $this->api_secret,
				'purchase' => array(
					'transaction_id'   => $order->get_order_number(),
					'email_address'    => $order->get_billing_email(),
					'first_name'       => $order->get_billing_first_name(),
					'currency'         => $order->get_currency(),
					'transaction_time' => $order->get_date_created()->date( 'Y-m-d H:i:s' ),
					'subtotal'         => (double) $order->get_subtotal(),
					'tax'              => (double) $order->get_total_tax( 'edit' ),
					'shipping'         => (double) $order->get_shipping_total( 'edit' ),
					'discount'         => (double) $order->get_discount_total( 'edit' ),
					'total'            => (double) $order->get_total( 'edit' ),
					'status'           => 'paid',
					'products'         => $products,
					'integration'      => 'WooCommerce'
				)
			);

			$query_args = is_null( $this->api_key ) ? array() : array(
				'api_key' => $this->api_key,
			);
			$body = $purchase_options;
			$args = array( 'method' => 'POST' );

			$this->debug_log( 'send payment request: ' . print_r( $purchase_options, true ) );

			$response = ckwc_convertkit_api_request( 'purchases', $query_args, $body, $args );

			if ( is_wp_error( $response ) ){
				$order->add_order_note( 'Send payment to ConvertKit error: ' . $response->get_error_code() . ' ' . $response->get_error_message(), 0, 'ConvertKit plugin' );
				$this->debug_log( 'Send payment response WP Error: ' . $response->get_error_code() . ' ' . $response->get_error_message() );
			} else {
				$order->add_order_note( 'Payment data sent to ConvertKit', 0, false );
				$this->debug_log( 'send payment response: ' . print_r( $response, true ) );
			}

		}
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
	public function process_convertkit_subscriptions( $subscriptions, $email, $name, $order_id ) {
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
	public function process_item_subscription( $subscription, $email, $name, $order_id ) {
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
	 * @param bool|WC_Order|WC_Order_Refund $order
	 *
	 * @return string
	 */
	public function email( $order ) {
		$email = version_compare( WC()->version, '3.0.0', '>=' ) ? $order->get_billing_email() : $order->billing_email;

		return apply_filters( 'convertkit_for_woocommerce_email', $email, $order );
	}

	/**
	 * @param bool|WC_Order|WC_Order_Refund $order
	 *
	 * @return string
	 */
	public function first_name( $order ) {
		$first_name = version_compare( WC()->version, '3.0.0',
		                               '>=' ) ? $order->get_billing_first_name() : $order->billing_first_name;

		return apply_filters( 'convertkit_for_woocommerce_first_name', $first_name, $order );
	}

	/**
	 * @param bool|WC_Order|WC_Order_Refund $order
	 *
	 * @return string
	 */
	public function last_name( $order ) {
		$last_name = version_compare( WC()->version, '3.0.0',
		                              '>=' ) ? $order->get_billing_last_name() : $order->billing_last_name;

		return apply_filters( 'convertkit_for_woocommerce_last_name', $last_name, $order );
	}

	/**
	 * @param string $first_name
	 * @param string $last_name
	 *
	 * @return string
	 */
	public function name_format( $first_name, $last_name ) {
		switch ( $this->name_format ) {
			case 'first':
				return $first_name;
				break;
			case 'last':
				return $last_name;
				break;
			default:
				return sprintf( "%s %s", $first_name, $last_name );
				break;

		}
	}

	/**
	 * Send order data to ConvertKit
	 *
	 * @param int $order_id
	 */
	/**
	 * @param int $order_id
	 * @param string $status_old
	 * @param string $status_new
	 * @param WC_Order $order
	 */
	public function send_payment( $order_id, $status_old, $status_new, $order ) {
		$api_key_correct = ! empty( $this->api_key );
		$status_correct  = $status_new === $this->event;

		// When the subscribe event is "Order Created", the "pending" status we are looking for will only last a bit. This line ensures we don't miss it.
		if ( 'pending' === $this->event && 'pending' === $status_old ) {
			$status_correct = true;
		}

		$order = wc_get_order( $order_id );

		if ( $api_key_correct && $status_correct && ! is_wp_error( $order ) && $order ) {

			$products = array();

			foreach( $order->get_items( ) as $item_key => $item ) {
				if ( ! $item->get_product() ) {
					continue;
				}
				$products[] = array(
				        'pid'        => $item->get_product()->get_id(),
						'lid'        => $item_key,
						'name'       => $item->get_name(),
						'sku'        => $item->get_product()->get_sku(),
						'unit_price' => $item->get_product()->get_price(),
						'quantity'   => $item->get_quantity(),
					);
			}

			$purchase_options = array(
				'api_secret' => $this->api_secret,
				'purchase' => array(
					'transaction_id'   => $order->get_order_number(),
					'email_address'    => $order->get_billing_email(),
					'first_name'       => $order->get_billing_first_name(),
					'currency'         => $order->get_currency(),
					'transaction_time' => $order->get_date_created()->date( 'Y-m-d H:i:s' ),
					'subtotal'         => (double) $order->get_subtotal(),
					'tax'              => (double) $order->get_total_tax( 'edit' ),
					'shipping'         => (double) $order->get_shipping_total( 'edit' ),
					'discount'         => (double) $order->get_discount_total( 'edit' ),
					'total'            => (double) $order->get_total( 'edit' ),
					'status'           => 'paid',
					'products'         => $products,
					'integration'      => 'WooCommerce'
				)
			);

			$query_args = is_null( $this->api_key ) ? array() : array(
				'api_key' => $this->api_key,
			);
			$body = $purchase_options;
			$args = array( 'method' => 'POST' );

			$this->debug_log( 'send payment request: ' . print_r( $purchase_options, true ) );

			$response = ckwc_convertkit_api_request( 'purchases', $query_args, $body, $args );

			if ( is_wp_error( $response ) ){
				$order->add_order_note( 'Send payment to ConvertKit error: ' . $response->get_error_code() . ' ' . $response->get_error_message(), 0, 'ConvertKit plugin' );
				$this->debug_log( 'Send payment response WP Error: ' . $response->get_error_code() . ' ' . $response->get_error_message() );
			} else {
			    $order->add_order_note( 'Payment data sent to ConvertKit', 0, false );
				$this->debug_log( 'send payment response: ' . print_r( $response, true ) );
			}

		}
	}

	public function refresh_subscription_options() {

		$key   = 'subscription';
		$field = $this->get_field_key( $key );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You don\'t have enough permissions.', 'convertkit' ) );
			wp_die();
		}

		delete_transient( 'ckwc_subscription_options' );

		$options = ckwc_force_get_subscription_options();

		ob_start();

		?>
        <select class="select" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="">
            <option <?php selected( '', $this->get_option( $key ) ); ?> value=""><?php _e( 'Select a subscription option...', 'woocommerce-convertkit' ); ?></option>
			<?php foreach ( $options as $option_group ) {
				if ( empty( $option_group['options'] ) ) {
					continue;
				} ?>
                <optgroup label="<?php echo esc_attr( $option_group['name'] ); ?>">
					<?php foreach ( $option_group['options'] as $id => $name ) {
						$value = "{$option_group['key']}:{$id}"; ?>
                        <option <?php selected( $value, $this->get_option( $key ) ); ?> value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $name ); ?></option>
					<?php } ?>
                </optgroup>
			<?php } ?>
        </select>
		<?php

		$html = ob_get_clean();

		wp_send_json_success( $html );
		wp_die();
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
	 * Plugin Links.
	 *
	 * @param $links
	 * @return array
	 */
	function plugin_links( $links ) {

		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=integration&section=ckwc' ) . '">' . __( 'Settings', 'woocommerce-convertkit' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}


}

require_once( 'functions/integration.php' );

/**
 * @param array $integrations
 *
 * @return array
 */
function ckwc_woocommerce_integrations( $integrations ) {
	$integrations[] = 'CKWC_Integration';

	return $integrations;
}
add_filter( 'woocommerce_integrations', 'ckwc_woocommerce_integrations' );
