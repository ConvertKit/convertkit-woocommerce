<?php

if(!defined('ABSPATH')) { exit; }

class CKWC_Integration extends WC_Integration {
	public function __construct() {
		$this->id                 = 'ckwc';
		$this->method_title       = __('ConvertKit');
		$this->method_description = __('Enter your ConvertKit settings below to control how WooCommerce integrates with your ConvertKit account.');

		// Initialize form fields
		$this->init_form_fields();

		// Initialize settings
		$this->init_settings();

		// API interaction
		$this->api_key      = $this->get_option('api_key');
		$this->subscription = $this->get_option('subscription');

		// Enabled and when it should take place
		$this->enabled      = $this->get_option('enabled');
		$this->event        = $this->get_option('event');

		// Opt-in field
		$this->display_opt_in  = $this->get_option('display_opt_in');
		$this->opt_in_label    = $this->get_option('opt_in_label');
		$this->opt_in_status   = $this->get_option('opt_in_status');
		$this->opt_in_location = $this->get_option('opt_in_location');

		if(is_admin()) {
			add_filter( 'plugin_action_links_' . CKWC_PLUGIN_BASENAME, array( $this, 'plugin_links') );


			add_action("woocommerce_update_options_integration_{$this->id}", array($this, 'process_admin_options'));

			add_filter("woocommerce_settings_api_sanitized_fields_{$this->id}", array($this, 'sanitize_settings'));

			add_action('add_meta_boxes_product', array($this, 'add_meta_boxes'));
			add_action('save_post_product', array($this, 'save_product'));
		}

		if('yes' === $this->enabled && 'yes' === $this->display_opt_in) {
			add_filter('woocommerce_checkout_fields', array($this, 'add_opt_in_checkbox'));
		}

		if('yes' === $this->enabled) {
			add_action('woocommerce_checkout_update_order_meta',  array($this, 'save_opt_in_checkbox'));
			add_action('woocommerce_process_shop_order_meta', array($this, 'save_opt_in_checkbox'));

			add_action('woocommerce_checkout_update_order_meta',  array($this, 'order_status'), 99999, 1);
			add_action('woocommerce_order_status_changed',        array($this, 'order_status'), 99999, 3);
		}

	}

	#region Product Integration

	public function add_meta_boxes($post) {
		add_meta_box('ckwc', __('Convert Kit Integration'), array($this, 'display_meta_box'), null, $context = 'side', $priority = 'default');
	}

	public function display_meta_box($post) {
		$subscription = get_post_meta($post->ID, 'ckwc_subscription', true);
		$options      = empty($this->api_key) ? false : ckwc_get_subscription_options();

		include('views/meta-box.php');
	}

	public function save_product($post_id) {
		$data = stripslashes_deep($_POST);

		if(isset($data['ckwc_nonce']) && wp_verify_nonce($data['ckwc_nonce'], 'ckwc') && isset($data['ckwc_subscription'])) {
			update_post_meta($post_id, 'ckwc_subscription', $data['ckwc_subscription']);
		}
	}

	#endregion Product Integration

	#region Create Form Fields and Settings

	public function init_form_fields(){
		$this->form_fields = array(
			'enabled' => array(
				'title'       => __('Enable/Disable'),
				'type'        => 'checkbox',
				'label'       => __('Enable ConvertKit integration'),
				'default'     => 'no',
			),

			'event' => array(
				'title'       => __('Subscribe Event'),
				'type'        => 'select',
				'default'     => 'pending',
				'description' => __('When should customers be subscribed?'),
				'desc_tip'    => false,
				'options'     => array(
					'pending'    => __('Order Created'),
					'processing' => __('Order Processing'),
					'completed'  => __('Order Completed'),
				),
			),

			'display_opt_in' => array(
				'title'       => __('Display Opt-In Checkbox'),
				'label'       => __('Display an Opt-In checkbox on checkout'),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __('If enabled, customers will only be subscribed if the "Opt-In" checkbox presented on checkout is checked.'),
				'desc_tip'    => false,
			),

			'opt_in_label' => array(
				'title'       => __('Opt-In Checkbox Label'),
				'type'        => 'text',
				'default'     => __('I want to subscribe to the newsletter'),
				'description' => __('Optional (only used if the above field is checked): Customize the label next to the opt-in checkbox.'),
				'desc_tip'    => false,
			),

			'opt_in_status' => array(
				'title'       => __('Opt-In Checkbox<br />Default Status'),
				'type'        => 'select',
				'default'     => 'checked',
				'description' => __('The default state of the opt-in checkbox'),
				'desc_tip'    => false,
				'options'     => array(
					'checked'   => __('Checked'),
					'unchecked' => __('Unchecked'),
				),
			),

			'opt_in_location' => array(
				'title'       => __('Opt-In Checkbox<br />Display Location'),
				'type'        => 'select',
				'default'     => 'billing',
				'description' => __('Where to display the opt-in checkbox on the checkout page (under Billing Info or Order Info).'),
				'desc_tip'    => false,
				'options'     => array(
					'billing' => __('Billing'),
					'order'   => __('Order'),
				),
			),

			'api_key' => array(
				'title'       => __('API Key'),
				'type'        => 'text',
				'default'     => '',
				'description' => sprintf(__('If you already have an account, <a href="%s" target="_blank">click here to retrieve your API Key</a>.<br />If you don\'t have a ConvertKit account, you can <a href="%s" target="_blank">sign up for one here</a>.'), esc_attr(esc_html('https://app.convertkit.com/account/edit')), esc_attr(esc_url('http://convertkit.com/pricing/'))),
				'desc_tip'    => false,
			),

			'subscription' => array(
				'title'       => __('Subscription'),
				'type'        => 'subscription',
				'default'     => '',
				'description' => __('Customers will be added to the selected item'),
			),

			'debug' => array(
				'title'       => __('Debug'),
				'type'        => 'checkbox',
				'label'       => __('Write data to a log file'),
				'description' => 'You can view the log file by going to WooCommerce > Settings > Logs then selecting convertkit.',
				'default'     => 'no',
			),
		);


		ob_start();
		include('resources/integration.js');
		$code = ob_get_clean();

		wc_enqueue_js($code);
	}

	public function generate_subscription_html($key, $data) {
		$field    = $this->get_field_key($key);
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
			'options'           => array()
		);

		$data = wp_parse_args($data, $defaults);

		$options = false;

		if(!empty($this->api_key)) {
			$options = ckwc_get_subscription_options();
		}

		ob_start();

		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr($field); ?>"><?php echo wp_kses_post($data['title']); ?></label>
				<?php echo $this->get_tooltip_html($data); ?>
			</th>
			<td class="forminp">
				<?php if($options) { ?>
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post($data['title']); ?></span></legend>
					<select class="select <?php echo esc_attr($data['class']); ?>" name="<?php echo esc_attr($field); ?>" id="<?php echo esc_attr($field); ?>" style="<?php echo esc_attr($data['css']); ?>" <?php disabled($data['disabled'], true); ?> <?php echo $this->get_custom_attribute_html($data); ?>>
						<option <?php selected('', $this->get_option($key)); ?> value=""><?php _e('Select a subscription option...'); ?></option>
						<?php foreach($options as $option_group) { if(empty($option_group['options'])) { continue; } ?>
						<optgroup label="<?php echo esc_attr($option_group['name']); ?>">
							<?php foreach($option_group['options'] as $id => $name) { $value = "{$option_group['key']}:{$id}"; ?>
							<option <?php selected($value, $this->get_option($key)); ?> value="<?php echo esc_attr($value); ?>"><?php echo esc_html($name); ?></option>
							<?php } ?>
						</optgroup>
						<?php } ?>
					</select>
					<?php echo $this->get_description_html($data); ?>
				</fieldset>
				<?php } else { ?>
				<p class="description"><?php _e('Please provide a valid ConvertKit API Key.'); ?></p>
				<?php } ?>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	#endregion Create Form Fields and Settings

	#region Sanitize Settings

	public function sanitize_settings($settings) {
		$settings['api_key'] = trim($settings['api_key']);

		return $settings;
	}

	#endregion Sanitize Settings

	#region Validate Settings

	public function validate_api_key_field($key) {
		$field = $this->get_field_key($key);
		$value = $_POST[$field];

		if(empty($value)) {
			$this->errors[$key] = __('Please provide your ConvertKit API Key.');
		} else {
			$forms = ckwc_convertkit_api_get_forms($value);

			if(is_wp_error($forms)) {
				$this->errors[$key] = __('Your ConvertKit API Key appears to be invalid. Please double check the value.');
			}
		}

		return $value;
	}

	#endregion Validate Settings

	#region Display Errors

	public function display_errors() {
		if(!empty($this->errors)) {
			foreach($this->errors as $key => $value) {
				printf('<div class="error" id="ckwc_error_%s"><p>%s</p></div>', esc_attr($key), esc_html($value));
			}
		}
	}

	#endregion Display Errors

	#region Frontend Checkout Fields

	public function add_opt_in_checkbox($fields) {
		$section = 'billing' === $this->opt_in_location ? 'billing' : 'order';

		$fields[$section]['ckwc_opt_in'] = array(
			'type'    => 'checkbox',
			'label'   => $this->opt_in_label,
			'default' => 'checked' === $this->opt_in_status,
		);

		return $fields;
	}

	public function save_opt_in_checkbox($order_id) {
		$opt_in = ('no' === $this->display_opt_in || isset($_POST['ckwc_opt_in'])) ? 'yes' : 'no';

		update_post_meta($order_id, 'ckwc_opt_in', $opt_in);
	}

	#endregion Frontend Checkout Fields

	#region Process Subscription

	public function order_status($order_id, $status_old = 'new', $status_new = 'pending') {
		$api_key_correct = !empty($this->api_key);
		$status_correct  = $status_new === $this->event;
		$opt_in_correct  = 'yes' === get_post_meta($order_id, 'ckwc_opt_in', 'no');
		if($api_key_correct && $status_correct && $opt_in_correct) {
			$order = wc_get_order($order_id);
			$items = $order->get_items();
			$name  = sprintf("%s %s", $order->billing_first_name, $order->billing_last_name);
			$email = $order->billing_email;

			$subscriptions = array($this->subscription);

			foreach($items as $item) {
				$subscriptions[] = get_post_meta($item['product_id'], 'ckwc_subscription', true);
			}

			$subscriptions = array_filter(array_unique($subscriptions));

			foreach($subscriptions as $subscription) {
				$subscription_parts    = explode(':', $subscription);
				$subscription_type     = $subscription_parts[0];
				$subscription_id       = $subscription_parts[1];
				$subscription_function = "ckwc_convertkit_api_add_subscriber_to_{$subscription_type}";

				if(function_exists($subscription_function)) {
					$response = call_user_func($subscription_function, $subscription_id, $email, $name);

					$debug = $this->get_option( 'debug' );
					if ( 'yes' == $debug ) {
						$this->debug_log( "API call: " . $subscription_type . "\n" . "Response: \n" .
							print_r( $response, true) );
					}

				}
			}
		}
	}

	#endregion Process Subscription


	/**
	 * Write API request results to a debug log
	 * @param $message
	 */
	public function debug_log( $message ) {

		$debug = $this->get_option( 'debug' );
		if ( class_exists( 'WC_Logger' ) && ( 'yes' == $debug ) ){
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

require_once('functions/integration.php');

function ckwc_woocommerce_integrations($integrations) {
	$integrations[] = 'CKWC_Integration';

	return $integrations;
}
add_filter('woocommerce_integrations', 'ckwc_woocommerce_integrations');
