<?php
/**
 * ConvertKit for WooCommerce class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Main ConvertKit for WooCommerce class, which registers the WooCommerce integration
 * and initialises required classes depending on the environment (frontend site, admin etc).
 *
 * @package CKWC
 * @author ConvertKit
 */
class WP_CKWC {

	/**
	 * Holds the class object.
	 *
	 * @since   1.4.2
	 *
	 * @var     object
	 */
	public static $instance;

	/**
	 * Holds singleton initialized classes that include
	 * action and filter hooks.
	 *
	 * @since   1.4.2
	 *
	 * @var     array
	 */
	private $classes = array();

	/**
	 * Constructor. Acts as a bootstrap to load the rest of the plugin
	 *
	 * @since   1.4.2
	 */
	public function __construct() {

		// Register integration.
		add_filter( 'woocommerce_integrations', array( $this, 'woocommerce_integrations_register' ) );

		// Initialize.
		add_action( 'woocommerce_init', array( $this, 'woocommerce_init' ) );

		// Load language files.
		add_action( 'init', array( $this, 'load_language_files' ) );

	}

	/**
	 * Register this Plugin's CKWC_Integration class as a WooCommerce Integration.
	 *
	 * @since   1.0.0
	 *
	 * @param   array $integrations   WooCommerce Integrations.
	 * @return  array                   WooCommerce Integrations
	 */
	public function woocommerce_integrations_register( $integrations ) {

		// Load integration.
		require_once CKWC_PLUGIN_PATH . '/includes/class-ckwc-integration.php';

		// Register integration.
		$integrations[] = 'CKWC_Integration';

		return $integrations;

	}

	/**
	 * Initialize admin, frontend and global Plugin classes when WooCommerce initializes,
	 * after WooCommerce has loaded its integrations.
	 *
	 * @since   1.0.0
	 */
	public function woocommerce_init() {

		// Initialize class(es) to register hooks.
		$this->initialize_admin();
		$this->initialize_frontend();
		$this->initialize_global();

	}

	/**
	 * Initialize classes for the WordPress Administration interface
	 *
	 * @since   1.4.2
	 */
	private function initialize_admin() {

		// Bail if this request isn't for the WordPress Administration interface.
		if ( ! is_admin() ) {
			return;
		}

		$this->classes['admin_ajax']    = new CKWC_Admin_AJAX();
		$this->classes['admin_plugin']  = new CKWC_Admin_Plugin();
		$this->classes['admin_product'] = new CKWC_Admin_Product();

		/**
		 * Initialize integration classes for the WordPress Administration interface.
		 *
		 * @since   1.4.2
		 */
		do_action( 'convertkit_for_woocommerce_initialize_admin' );

	}

	/**
	 * Initialize classes for the frontend web site
	 *
	 * @since   1.4.2
	 */
	private function initialize_frontend() {

		// Bail if this request isn't for the frontend web site.
		if ( is_admin() ) {
			return;
		}

		$this->classes['checkout'] = new CKWC_Checkout();

		/**
		 * Initialize integration classes for the frontend web site.
		 *
		 * @since   1.4.2
		 */
		do_action( 'convertkit_for_woocommerce_initialize_frontend' );

	}

	/**
	 * Initialize classes required globally, across the WordPress Administration, CLI, Cron and Frontend
	 * web site.
	 *
	 * @since   1.4.2
	 */
	private function initialize_global() {

		$this->classes['order']            = new CKWC_Order();
		$this->classes['review_request']   = new CKWC_Review_Request( 'ConvertKit for WooCommerce', 'convertkit-for-woocommerce' );
		$this->classes['wc_subscriptions'] = new CKWC_WC_Subscriptions();

		/**
		 * Initialize integration classes for the frontend web site.
		 *
		 * @since   1.4.2
		 */
		do_action( 'convertkit_for_woocommerce_initialize_global' );

	}

	/**
	 * Loads plugin textdomain
	 *
	 * @since   1.0.0
	 */
	public function load_language_files() {

		load_plugin_textdomain( 'woocommerce-convertkit', false, basename( dirname( CKWC_PLUGIN_FILE ) ) . '/languages/' ); // @phpstan-ignore-line

	}

	/**
	 * Returns the given class
	 *
	 * @since   1.4.2
	 *
	 * @param   string $name   Class Name.
	 * @return  object          Class Object
	 */
	public function get_class( $name ) {

		// If the class hasn't been loaded, throw a WordPress die screen
		// to avoid a PHP fatal error.
		if ( ! isset( $this->classes[ $name ] ) ) {
			// Define the error.
			$error = new WP_Error(
				'convertkit_for_woocommerce_get_class',
				sprintf(
					/* translators: %1$s: PHP class name */
					__( 'ConvertKit for WooCommerce Error: Could not load Plugin class <strong>%1$s</strong>', 'woocommerce-convertkit' ),
					$name
				)
			);

			// Depending on the request, return or display an error.
			// Admin UI.
			if ( is_admin() ) {
				wp_die(
					esc_attr( $error->get_error_message() ),
					esc_html__( 'ConvertKit for WooCommerce Error', 'woocommerce-convertkit' ),
					array(
						'back_link' => true,
					)
				);
			}

			// Cron / CLI.
			return $error;
		}

		// Return the class object.
		return $this->classes[ $name ];

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since   1.4.2
	 *
	 * @return  object Class.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) { // @phpstan-ignore-line
			self::$instance = new self();
		}

		return self::$instance;

	}

}
