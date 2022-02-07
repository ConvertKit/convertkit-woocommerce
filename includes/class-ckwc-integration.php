<?php
/**
 * ConvertKit WooCommerce Integration class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Registers ConvertKit as an integration with WooCommerce, accessible at
 * WooCommerce > Settings > Integration > ConvertKit.
 *
 * @package CKWC
 * @author ConvertKit
 */
class CKWC_Integration extends WC_Integration {

	/**
	 * Constructor
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		// Define the ID, Title and Description of this Integration.
		$this->id                 = 'ckwc';
		$this->method_title       = __( 'ConvertKit', 'woocommerce-convertkit' );
		$this->method_description = __( 'Enter your ConvertKit settings below to control how WooCommerce integrates with your ConvertKit account.', 'woocommerce-convertkit' );

		// Initialize form fields and settings.
		$this->init_form_fields();
		$this->init_settings();

		// Load Admin screens, save settings.
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( "woocommerce_update_options_integration_{$this->id}", array( $this, 'process_admin_options' ) );
			add_filter( "woocommerce_settings_api_sanitized_fields_{$this->id}", array( $this, 'sanitize_settings' ) );
		}

	}

	/**
	 * Defines the fields to display on this integration's screen at WooCommerce > Settings > Integration > ConvertKit.
	 *
	 * Also loads JS for conditionally showing UI settings, based on the value of other settings.
	 *
	 * @since   1.4.2
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			// Enable/Disable entire integration.
			'enabled'         => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-convertkit' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable ConvertKit integration', 'woocommerce-convertkit' ),
				'default' => 'no',
			),

			// API Key and Secret.
			'api_key'         => array(
				'title'       => __( 'API Key', 'woocommerce-convertkit' ),
				'type'        => 'text',
				'default'     => '',
				'description' =>
					sprintf(
						/* translators: %1$s: Link to ConvertKit Account, %2$s: <br>, %3$s Link to ConvertKit Signup */
						esc_html__( '%1$s Required for proper plugin function. %2$s Don\'t have a ConvertKit account? %3$s', 'woocommerce-convertkit' ),
						'<a href="' . esc_url( ckwc_get_api_key_url() ) . '" target="_blank">' . esc_html__( 'Get your ConvertKit API Key.', 'woocommerce-convertkit' ) . '</a>',
						'<br />',
						'<a href="' . esc_url( ckwc_get_signup_url() ) . '" target="_blank">' . esc_html__( 'Sign up here.', 'woocommerce-convertkit' ) . '</a>'
					),
				'desc_tip'    => false,

				// The setting name that needs to be checked/enabled for this setting to display. Used by JS to toggle visibility.
				'class'       => 'enabled',
			),
			'api_secret'      => array(
				'title'       => __( 'API Secret', 'woocommerce-convertkit' ),
				'type'        => 'text',
				'default'     => '',
				'description' =>
					sprintf(
						/* translators: %1$s: Link to ConvertKit Account, %2$s: <br>, %3$s Link to ConvertKit Signup */
						esc_html__( '%1$s Required for proper plugin function. %2$s Don\'t have a ConvertKit account? %3$s', 'woocommerce-convertkit' ),
						'<a href="' . esc_url( ckwc_get_api_key_url() ) . '" target="_blank">' . esc_html__( 'Get your ConvertKit API Secret.', 'woocommerce-convertkit' ) . '</a>',
						'<br />',
						'<a href="' . esc_url( ckwc_get_signup_url() ) . '" target="_blank">' . esc_html__( 'Sign up here.', 'woocommerce-convertkit' ) . '</a>'
					),
				'desc_tip'    => false,

				// The setting name that needs to be checked/enabled for this setting to display. Used by JS to toggle visibility.
				'class'       => 'enabled',
			),

			// Subscribe.
			'event'           => array(
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

				// The setting name that needs to be checked/enabled for this setting to display. Used by JS to toggle visibility.
				'class'       => 'enabled subscribe',
			),
			'subscription'    => array(
				'title'       => __( 'Subscription', 'woocommerce-convertkit' ),
				'type'        => 'subscription',
				'default'     => '',
				'description' => __( 'The ConvertKit Form, Tag or Sequence to subscribe Customers to.', 'woocommerce-convertkit' ),

				// The setting name that needs to be checked/enabled for this setting to display. Used by JS to toggle visibility.
				'class'       => 'enabled subscribe',
			),
			'name_format'     => array(
				'title'       => __( 'Name Format', 'woocommerce-convertkit' ),
				'type'        => 'select',
				'default'     => 'first',
				'description' => __( 'How should the customer name be sent to ConvertKit?', 'woocommerce-convertkit' ),
				'desc_tip'    => false,
				'options'     => array(
					'first' => __( 'Billing First Name', 'woocommerce-convertkit' ),
					'last'  => __( 'Billing Last Name', 'woocommerce-convertkit' ),
					'both'  => __( 'Billing First Name + Billing Last Name', 'woocommerce-convertkit' ),
				),

				// The setting name that needs to be checked/enabled for this setting to display. Used by JS to toggle visibility.
				'class'       => 'enabled subscribe',
			),

			// Subscribe: Display Opt In Checkbox Settings.
			'display_opt_in'  => array(
				'title'       => __( 'Opt-In Checkbox', 'woocommerce-convertkit' ),
				'label'       => __( 'Display an Opt-In checkbox on checkout', 'woocommerce-convertkit' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __(
					'If enabled, customers will <strong>only</strong> be subscribed to the chosen Forms, Tags and Sequences if they check the "Opt-In" checkbox at checkout.<br />
									  If disabled, customers will <strong>always</strong> be subscribed to the chosen Forms, Tags and Sequences at checkout.',
					'woocommerce-convertkit'
				),
				'desc_tip'    => false,

				// The setting name that needs to be checked/enabled for this setting to display. Used by JS to toggle visibility.
				'class'       => 'enabled subscribe',
			),
			'opt_in_label'    => array(
				'title'       => __( 'Opt-In Checkbox: Label', 'woocommerce-convertkit' ),
				'type'        => 'text',
				'default'     => __( 'I want to subscribe to the newsletter', 'woocommerce-convertkit' ),
				'description' => __( 'Optional (only used if the above field is checked): Customize the label next to the opt-in checkbox.', 'woocommerce-convertkit' ),
				'desc_tip'    => false,

				// The setting name that needs to be checked/enabled for this setting to display. Used by JS to toggle visibility.
				'class'       => 'enabled subscribe display_opt_in',
			),
			'opt_in_status'   => array(
				'title'       => __( 'Opt-In Checkbox: Default Status', 'woocommerce-convertkit' ),
				'type'        => 'select',
				'default'     => 'checked',
				'description' => __( 'The default state of the opt-in checkbox.', 'woocommerce-convertkit' ),
				'desc_tip'    => false,
				'options'     => array(
					'checked'   => __( 'Checked', 'woocommerce-convertkit' ),
					'unchecked' => __( 'Unchecked', 'woocommerce-convertkit' ),
				),

				// The setting name that needs to be checked/enabled for this setting to display. Used by JS to toggle visibility.
				'class'       => 'enabled subscribe display_opt_in',
			),
			'opt_in_location' => array(
				'title'       => __( 'Opt-In Checkbox: Display Location', 'woocommerce-convertkit' ),
				'type'        => 'select',
				'default'     => 'billing',
				'description' => __( 'Where to display the opt-in checkbox on the checkout page (under Billing Info or Order Info).', 'woocommerce-convertkit' ),
				'desc_tip'    => false,
				'options'     => array(
					'billing' => __( 'Billing', 'woocommerce-convertkit' ),
					'order'   => __( 'Order', 'woocommerce-convertkit' ),
				),

				// The setting name that needs to be checked/enabled for this setting to display. Used by JS to toggle visibility.
				'class'       => 'enabled subscribe display_opt_in',
			),

			// Purchase Data.
			'send_purchases'  => array(
				'title'       => __( 'Purchase Data', 'woocommerce-convertkit' ),
				'label'       => __( 'Send purchase data to ConvertKit.', 'woocommerce-convertkit' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __(
					'If enabled, the customer\'s order data will be sent to ConvertKit. Their email address will always be subscribed to ConvertKit, <strong>regardless of the Customer\'s opt in status.</strong><br />
									  If disabled, no order data will be sent to ConvertKit.',
					'woocommerce-convertkit'
				),
				'desc_tip'    => false,

				// The setting name that needs to be checked/enabled for this setting to display. Used by JS to toggle visibility.
				'class'       => 'enabled subscribe',
			),
			'send_old_purchases' => array(
				'title'       => __( 'Send Old Purchase Data', 'woocommerce-convertkit' ),
				'label'       => __( 'Send old purchase data to ConvertKit i.e. Orders that were created in WooCommerce prior to this Plugin being installed.', 'woocommerce-convertkit' ),
				'type'        => 'send_old_purchases',
				'default'     => 'no',
				'desc_tip'    => false,
			),

			// Debugging.
			'debug'           => array(
				'title'       => __( 'Debug', 'woocommerce-convertkit' ),
				'type'        => 'checkbox',
				'label'       => __( 'Write data to a log file', 'woocommerce-convertkit' ),
				'description' => sprintf(
					/* translators: %1$s: URL to Log File, %2$s: View Log File text */
					'<a href="%1$s" target="_blank">%2$s</a>',
					admin_url( 'admin.php?page=wc-status&tab=logs' ),
					__( 'View Log File', 'woocommerce-convertkit' )
				),
				'default'     => 'no',

				// The setting name that needs to be checked/enabled for this setting to display. Used by JS to toggle visibility.
				'class'       => 'enabled',
			),
		);

	}

	/**
	 * Enqueue Javascript for the Integration Settings screen.
	 *
	 * @since   1.4.2
	 */
	public function enqueue_scripts() {

		// Bail if we cannot determine if we are viewing the Integration Settings screen.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		// Get screen.
		$screen = get_current_screen();

		// Bail if we're not on the Integration Settings screen.
		if ( $screen->id !== 'woocommerce_page_wc-settings' ) {
			return;
		}

		// Enqueue JS.
		wp_enqueue_script( 'ckwc-integration', CKWC_PLUGIN_URL . 'resources/backend/js/integration.js', array( 'jquery' ), CKWC_PLUGIN_VERSION, true );

	}

	/**
	 * Output HTML for the Form / Tag setting.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $key    Setting Field Key.
	 * @param   array  $data   Setting Field Configuration.
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

		// If the integration isn't enabled, don't output a selection field.
		if ( ! $this->is_enabled() ) {
			ob_start();
			require_once CKWC_PLUGIN_PATH . '/views/backend/settings/subscription-disabled.php';
			return ob_get_clean();
		}

		// Get Forms, Tags and Sequences, refreshing them to fetch the latest data from the API.
		$forms = new CKWC_Resource_Forms();
		$forms->refresh();
		$sequences = new CKWC_Resource_Sequences();
		$sequences->refresh();
		$tags = new CKWC_Resource_Tags();
		$tags->refresh();

		// Get current subscription setting and other settings to render the subscription dropdown field.
		$subscription = array(
			'id'        => 'woocommerce_ckwc_subscription',
			'class'     => 'select ' . $data['class'],
			'name'      => $field,
			'value'     => $this->get_option( $key ),
			'forms'     => $forms,
			'tags'      => $tags,
			'sequences' => $sequences,
		);

		ob_start();
		require_once CKWC_PLUGIN_PATH . '/views/backend/settings/subscription.php';
		return ob_get_clean();

	}

	/**
	 * Output HTML for the Send Old Purchase Data button.
	 * 
	 * Conditionally renders the button if WooCommerce Orders exist that do not have a ckwc_purchase_data_id,
	 * meaning the Purchase Data option wasn't enabled in the past, and/or the Plugin wasn't installed
	 * in the past.
	 *
	 * @since   1.4.3
	 *
	 * @param   string $key    Setting Field Key.
	 * @param   array  $data   Setting Field Configuration.
	 */
	public function generate_send_old_purchases_html( $key, $data ) {

		// Fetch array of WooCommerce Order IDs that have not been sent to ConvertKit.
		$unsynced_order_ids =WP_CKWC()->get_class( 'order' )->get_orders_not_sent_to_convertkit();

		// If no Orders exist that do not have a ckwc_purchase_data_id, there's
		// no 'old' WooCommerce Orders to send to ConvertKit's Purchases endpoint.
		if ( ! $unsynced_order_ids ) {
			return;
		}

		// Return HTML for button.
		ob_start();
		require_once CKWC_PLUGIN_PATH . '/views/backend/settings/send-old-purchases.php';
		return ob_get_clean();

	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @since   1.0.0
	 *
	 * @param   array $settings   Plugin Settings.
	 * @return  array               Plugin Settings, sanitized
	 */
	public function sanitize_settings( $settings ) {

		$settings['api_key']    = trim( $settings['api_key'] );
		$settings['api_secret'] = trim( $settings['api_secret'] );
		return $settings;

	}

	/**
	 * Validate that the API Key is valid when saving settings.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $key        Setting Key.
	 * @param   string $api_key    API Key.
	 * @return  string              API Key
	 */
	public function validate_api_key_field( $key, $api_key ) { /* phpcs:ignore */

		// If the integration isn't enabled, don't validate the API Key.
		if ( ! isset( $_POST[ $this->plugin_id . $this->id . '_enabled' ] ) ) { /* phpcs:ignore */
			$this->resources_delete();
			return $api_key;
		}

		// Bail if the API Key has not been specified.
		if ( empty( $api_key ) ) {
			$this->resources_delete();
			WC_Admin_Settings::add_error( esc_html__( 'Please provide your ConvertKit API Key.', 'woocommerce-convertkit' ) );
			return $api_key;
		}

		// Get Forms to test that the API Key is valid.
		$api   = new CKWC_API(
			$api_key,
			$this->get_option( 'api_secret' ),
			$this->get_option_bool( 'debug' )
		);
		$forms = $api->get_forms();

		// Bail if an error occured.
		if ( is_wp_error( $forms ) ) {
			$this->resources_delete();
			WC_Admin_Settings::add_error( esc_html__( 'Your ConvertKit API Key appears to be invalid. Please double check the value.', 'woocommerce-convertkit' ) );
		}

		// Return API Key.
		return $api_key;

	}

	/**
	 * Whether the ConvertKit integration is enabled, meaning:
	 * - the 'Enable/Disable' option is checked,
	 * - an API Key and Secret are specified.
	 *
	 * @since   1.4.2
	 *
	 * @return  bool    Integration Enabled.
	 */
	public function is_enabled() {

		return ( $this->get_option_bool( 'enabled' ) && $this->option_exists( 'api_key' ) && $this->option_exists( 'api_secret' ) );

	}

	/**
	 * Returns the given integration setting value, converting 'yes' to true
	 * and any other value to false.
	 *
	 * @since   1.4.2
	 *
	 * @param   string $name   Setting Name.
	 * @return  bool
	 */
	public function get_option_bool( $name ) {

		$value = $this->get_option( $name );

		if ( $value === 'yes' ) {
			return true;
		}

		return false;

	}

	/**
	 * Returns whether the given setting value has a value, and isn't empty.
	 *
	 * @since   1.4.2
	 *
	 * @param   string $name   Setting Name.
	 * @return  bool
	 */
	public function option_exists( $name ) {

		return ! empty( $this->get_option( $name ) );

	}

	/**
	 * Deletes all cached Forms, Tags and Sequences from the options table.
	 *
	 * @since   1.4.2
	 */
	private function resources_delete() {

		$forms = new CKWC_Resource_Forms();
		$forms->delete();

		$tags = new CKWC_Resource_Tags();
		$tags->delete();

		$sequences = new CKWC_Resource_Sequences();
		$sequences->delete();

	}

}
