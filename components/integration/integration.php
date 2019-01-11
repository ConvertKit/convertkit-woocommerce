<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class CKWC_Integration extends WC_Integration {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'ckwc';
		$this->method_title       = __( 'ConvertKit' );
		$this->method_description = __( 'Enter your ConvertKit settings below to control how WooCommerce integrates with your ConvertKit account.' );

		// Initialize form fields
		$this->init_form_fields();

		// Initialize settings
		$this->init_settings();

		// API interaction
		$this->api_key      = $this->get_option( 'api_key' );
		$this->api_secret      = $this->get_option( 'api_secret' );
		$this->subscription = $this->get_option( 'subscription' );

		// Enabled and when it should take place
		$this->enabled         = $this->get_option( 'enabled' );
		$this->event           = $this->get_option( 'event' );
		$this->send_purchases  = $this->get_option( 'send_purchases' );

		// Opt-in field
		$this->display_opt_in  = $this->get_option( 'display_opt_in' );
		$this->opt_in_label    = $this->get_option( 'opt_in_label' );
		$this->opt_in_status   = $this->get_option( 'opt_in_status' );
		$this->opt_in_location = $this->get_option( 'opt_in_location' );
		$this->name_format     = $this->get_option( 'name_format' );

		add_action( 'wp_ajax_ckwc_find_unsynced_orders', array( $this, 'find_unsynced_orders' ) );
		add_action( 'wp_ajax_ckwc_sync_orders', array( $this, 'sync_orders' ) );

		if ( is_admin() ) {
			add_filter( 'plugin_action_links_' . CKWC_PLUGIN_BASENAME, array( $this, 'plugin_links' ) );

			add_action( "woocommerce_update_options_integration_{$this->id}", array( $this, 'process_admin_options' ) );

			add_filter( "woocommerce_settings_api_sanitized_fields_{$this->id}", array( $this, 'sanitize_settings' ) );

			add_action( 'add_meta_boxes_product', array( $this, 'add_meta_boxes' ) );
			add_action( 'save_post_product', array( $this, 'save_product' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		}

		if ( 'yes' === $this->enabled && 'yes' === $this->display_opt_in ) {
			add_filter( 'woocommerce_checkout_fields', array( $this, 'add_opt_in_checkbox' ) );
		}

		if ( 'yes' === $this->enabled ) {
			add_action( 'woocommerce_checkout_update_order_meta',  array( $this, 'save_opt_in_checkbox' ) );

			add_action( 'woocommerce_checkout_update_order_meta',  array( $this, 'order_status' ), 99999, 1 );
			add_action( 'woocommerce_order_status_changed',        array( $this, 'order_status' ), 99999, 3 );

			if ( 'yes' === $this->send_purchases ){
				add_action( 'woocommerce_payment_complete',        array( $this, 'send_payment' ), 99999, 3 );
			}
		}

	}

	/**
	 * Sync Orders
	 */
	public function sync_orders() {
		$step  = $_GET['step'] ? absint( $_GET['step'] ) : 1;
		$total = $_GET['total'] ? absint( $_GET['total'] ) : 0;

		include 'class-ckwc-sync-orders-batch.php';

		if ( 0 === $total ) {
			$total = count( get_option( 'ckwc_unsynched_orders', array() ) );
        }

		$batch = new CKWC_Sync_Orders_Batch( $step, $total, 10 );
		$batch->method = array( $this, 'send_payment' );
		$batch->process();
		$progress  = $batch->get_progress();
		$done      = false;
		$next_step = $step + 1;
		$unsynced  = $batch->get_unsynced_orders();
		update_option( 'ckwc_unsynched_orders', $unsynced, false );

		if ( 100 === $progress ) {
			$done = true;
		}

		wp_send_json_success( array(
			'step'     => $next_step,
			'total'    => $total,
			'done'     => $done,
			'progress' => $progress,
		));
		wp_die();
    }

	/**
	 * Find Unsynced Orders
	 */
	public function find_unsynced_orders() {
        $step  = $_GET['step'] ? absint( $_GET['step'] ) : 1;
        $total = $_GET['total'] ? absint( $_GET['total'] ) : 0;

        include 'class-ckwc-unsynced-batch.php';

        $batch = new CKWC_Unsynced_Batch( $step, $total );
        $batch->process();
        $progress  = $batch->get_progress();
        $done      = false;
        $next_step = $step + 1;
        $unsynced  = $batch->get_unsynced_orders();
        update_option( 'ckwc_unsynched_orders', $unsynced, false );

        if ( 100 === $progress ) {
            $done = true;
        }

        wp_send_json_success( array(
                'step'     => $next_step,
                'total'    => $total,
                'done'     => $done,
                'unsynced' => count( $unsynced ),
        ));
        wp_die();
    }

	/**
	 * @param $hook
	 */
	public function admin_enqueue( $hook ) {

	    if ( 'woocommerce_page_wc-settings' !== $hook ) {
	        return;
	    }

	    if ( ! isset( $_GET['tab'] ) || 'integration' !== $_GET['tab'] ) {
	        return;
        }

        wp_enqueue_style( 'ckwc-css', plugin_dir_url( CKWC_PLUGIN_FILEPATH ) . '/components/integration/resources/integration.css' );
	    wp_enqueue_script( 'ckwc-js', plugin_dir_url( CKWC_PLUGIN_FILEPATH ) . '/components/integration/resources/integration.js', array( 'jquery' ), '1.0.6', true );
    }

	/**
     * Return the count of total orders.
     *
	 * @return int
	 */
    private function get_order_count() {
	    // No WHERE clause for a faster retrieveal.
	    $system_status    = new WC_REST_System_Status_Controller();
	    $post_type_counts = $system_status->get_post_type_counts();
	    $post_type_counts = array_values( array_filter( $post_type_counts, array( $this, 'only_order_type' ) ) );
	    return $post_type_counts ? $post_type_counts[0]->count : 0;
    }

	/**
     * Return an array element if it's an order type.
     *
     * @see CKWC_Integration::get_order_count
     *
	 * @param $object
	 *
	 * @return bool
	 */
    public function only_order_type( $object ) {
	    if ( 'shop_order' !== $object->type ) { return false; }
	    return true;
    }

	/**
	 * Output the gateway settings screen.
	 */
	public function admin_options() {
	    if ( ! isset( $_GET['screen'] ) || 'ckwc_sync_purchases' !== $_GET['screen'] ) {
		    parent::admin_options();
        } else {
		    $GLOBALS['hide_save_button'] = true;
		    $order_count = $this->get_order_count();
		    $unsynced    = get_option( 'ckwc_unsynched_orders', null );
		    echo '<h2>' . esc_html( $this->get_method_title() ) . ' - ' . __( 'Sync Purchases' ) . '</h2>';
		    echo '<div><input type="hidden" name="section" value="' . esc_attr( $this->id ) . '" /></div>';
		    ?>
            <a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=integration&section=ckwc' ) ?>" class="button button-secondary"><?php esc_html_e( 'Back to Settings' ); ?></a>
            <?php
            include_once 'views/orders-data.php';

        }
	}

	/**
	 * @param $post
	 */
	public function add_meta_boxes( $post ) {
		add_meta_box( 'ckwc', __( 'Convert Kit Integration' ), array( $this, 'display_meta_box' ), null, 'side', 'default' );
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
				'title'       => __( 'Enable/Disable' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable ConvertKit integration' ),
				'default'     => 'no',
			),

			'event' => array(
				'title'       => __( 'Subscribe Event' ),
				'type'        => 'select',
				'default'     => 'pending',
				'description' => __( 'When should customers be subscribed?' ),
				'desc_tip'    => false,
				'options'     => array(
					'pending'    => __( 'Order Created' ),
					'processing' => __( 'Order Processing' ),
					'completed'  => __( 'Order Completed' ),
				),
			),

			'display_opt_in' => array(
				'title'       => __( 'Display Opt-In Checkbox' ),
				'label'       => __( 'Display an Opt-In checkbox on checkout' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __( 'If enabled, customers will only be subscribed if the "Opt-In" checkbox presented on checkout is checked.' ),
				'desc_tip'    => false,
			),

			'opt_in_label' => array(
				'title'       => __( 'Opt-In Checkbox Label' ),
				'type'        => 'text',
				'default'     => __( 'I want to subscribe to the newsletter' ),
				'description' => __( 'Optional (only used if the above field is checked): Customize the label next to the opt-in checkbox.' ),
				'desc_tip'    => false,
			),

			'opt_in_status' => array(
				'title'       => __( 'Opt-In Checkbox<br />Default Status' ),
				'type'        => 'select',
				'default'     => 'checked',
				'description' => __( 'The default state of the opt-in checkbox' ),
				'desc_tip'    => false,
				'options'     => array(
					'checked'   => __( 'Checked' ),
					'unchecked' => __( 'Unchecked' ),
				),
			),

			'opt_in_location' => array(
				'title'       => __( 'Opt-In Checkbox<br />Display Location' ),
				'type'        => 'select',
				'default'     => 'billing',
				'description' => __( 'Where to display the opt-in checkbox on the checkout page (under Billing Info or Order Info).' ),
				'desc_tip'    => false,
				'options'     => array(
					'billing' => __( 'Billing' ),
					'order'   => __( 'Order' ),
				),
			),

			'api_key' => array(
				'title'       => __( 'API Key' ),
				'type'        => 'text',
				'default'     => '',
				// translators: this is a url to the ConvertKit site.
				'description' => sprintf( __( 'If you already have an account, <a href="%1$s" target="_blank">click here to retrieve your API Key</a>.<br />If you don\'t have a ConvertKit account, you can <a href="%2$s" target="_blank">sign up for one here</a>.' ), esc_attr( esc_html( 'https://app.convertkit.com/account/edit' ) ), esc_attr( esc_url( 'http://convertkit.com/pricing/' ) ) ),
				'desc_tip'    => false,
			),

			'api_secret' => array(
				'title'       => __( 'API Secret' ),
				'type'        => 'text',
				'default'     => '',
				// translators: this is a url to the ConvertKit site.
				'description' => sprintf( __( 'If you already have an account, <a href="%1$s" target="_blank">click here to retrieve your API Secret</a>.<br />If you don\'t have a ConvertKit account, you can <a href="%2$s" target="_blank">sign up for one here</a>.' ), esc_attr( esc_html( 'https://app.convertkit.com/account/edit' ) ), esc_attr( esc_url( 'http://convertkit.com/pricing/' ) ) ),
				'desc_tip'    => false,
			),

			'subscription' => array(
				'title'       => __( 'Subscription' ),
				'type'        => 'subscription',
				'default'     => '',
				'description' => __( 'Customers will be added to the selected item' ),
			),

			'name_format' => array(
				'title'       => __( 'Name Format' ),
				'type'        => 'select',
				'default'     => 'first',
				'description' => __( 'How should the customer name be sent to ConvertKit?' ),
				'desc_tip'    => false,
				'options'     => array(
					'first'   => __( 'Billing First Name' ),
					'last'    => __( 'Billing Last Name' ),
					'both'    => __( 'Billing First Name + Billing Last Name' ),
				),
			),

			'send_purchases' => array(
				'title'       => __( 'Purchases' ),
				'label'       => __( 'Send purchase data to ConvertKit.' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __( '' ),
				'desc_tip'    => false,
			),

			'sync_purchases' => array(
				'title'       => __( 'Sync Purchases' ),
				'label'       => __( 'Sync Old Purchases.' ),
				'type'        => 'ckwc_sync_purchases',
				'default'     => 'no',
				'description' => __( '' ),
				'desc_tip'    => false,
			),

			'debug' => array(
				'title'       => __( 'Debug' ),
				'type'        => 'checkbox',
				'label'       => __('Write data to a log file'),
				'description' => 'You can view the log file by going to WooCommerce > Status, click the Logs tab, then selecting convertkit.',
				'default'     => 'no',
			),
		);

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
                            <option <?php selected( '', $this->get_option( $key ) ); ?> value=""><?php _e( 'Select a subscription option...' ); ?></option>
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
                    <p class="description"><?php _e( 'Please provide a valid ConvertKit API Key.' ); ?></p>
				<?php } ?>
            </td>
        </tr>
		<?php

		return ob_get_clean();
	}

	/**
	 * @param $key
	 * @param $data
	 *
	 * @return string
	 */
	public function generate_ckwc_sync_purchases_html( $key, $data ) {

		ob_start();

		?>
        <tr valign="top">
            <th scope="row" class="titledesc" colspan="2">
                <a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=integration&section=ckwc&screen=ckwc_sync_purchases' ) ?>" class="button button-secondary"><?php esc_html_e( 'Sync Purchases' ); ?></a>
            </th>
        </tr>
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
			$this->errors[ $key ] = __( 'Please provide your ConvertKit API Key.' );
		} else {
			$forms = ckwc_convertkit_api_get_forms( $value );

			if ( is_wp_error( $forms ) ) {
				$this->errors[ $key ] = __( 'Your ConvertKit API Key appears to be invalid. Please double check the value.' );
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
	 * @param $order_id
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
			if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
				$email = $order->get_billing_email();
				$first_name  = $order->get_billing_first_name();
				$last_name  = $order->get_billing_last_name();

			} else {
				$email = $order->billing_email;
				$first_name  = $order->billing_first_name;
				$last_name  = $order->billing_last_name;
			}

			$email = apply_filters( 'convertkit_for_woocommerce_email', $email, $order);
			$first_name = apply_filters( 'convertkit_for_woocommerce_first_name', $first_name, $order);
			$last_name = apply_filters( 'convertkit_for_woocommerce_last_name', $last_name, $order);

			switch ( $this->name_format ) {
				case 'first':
					$name  = $first_name;
					break;
				case 'last':
					$name  = $last_name;
					break;
				default:
					$name  = sprintf("%s %s", $first_name, $last_name);
					break;

			}

			$subscriptions = array( $this->subscription );

			foreach ( $items as $item ) {
				$subscriptions[] = get_post_meta( $item['product_id'], 'ckwc_subscription', true );
			}

			$subscriptions = array_filter( array_unique( $subscriptions ) );

			foreach ( $subscriptions as $subscription ) {
				$subscription_parts    = explode( ':', $subscription );
				$subscription_type     = $subscription_parts[0];
				$subscription_id       = $subscription_parts[1];
				$subscription_function = "ckwc_convertkit_api_add_subscriber_to_{$subscription_type}";

				if ( function_exists( $subscription_function ) ) {
					$response = call_user_func( $subscription_function, $subscription_id, $email, $name );

					$debug = $this->get_option( 'debug' );
					if ( 'yes' === $debug ) {
						$this->debug_log( 'API call: ' . $subscription_type . "\nResponse: \n" . print_r( $response, true ) );
					}
				}
			}
		}// End if().
	}

	/**
	 * Send order data to ConvertKit
	 * @param $order_id
	 */
	public function send_payment( $order_id ){
		$api_key_correct = ! empty( $this->api_key );
		$order = wc_get_order( $order_id );
		if ( $api_key_correct && ! is_wp_error( $order ) && $order ) {

			$products = array();

			foreach( $order->get_items( ) as $item_key => $item ) {
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
				$unsynced = get_option( 'ckwc_unsynched_orders', array() );
				$unsynced[] = $order_id;
				update_option( 'ckwc_unsynched_orders', array_unique( $unsynced ), false );
			} else {
				$order->add_order_note( 'Payment data sent to ConvertKit', 0, false );
				$this->debug_log( 'send payment response: ' . print_r( $response, true ) );
				// Save the CK Purchase ID to Order.
				if ( isset( $response['id'] ) ) {
					$order->update_meta_data( '_ck_purchase_id', absint( $response['id'] ) );
					$order->save();
					return true;
				}
			}

		}
		return false;
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
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=integration' ) . '">' . __( 'Settings', 'wc-store-locator' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}


}

require_once( 'functions/integration.php' );

function ckwc_woocommerce_integrations( $integrations ) {
	$integrations[] = 'CKWC_Integration';
	return $integrations;
}
add_filter( 'woocommerce_integrations', 'ckwc_woocommerce_integrations' );
