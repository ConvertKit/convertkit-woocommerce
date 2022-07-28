<?php
/**
 * ConvertKit Admin Product class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Registers a metabox on WooCommerce Products and saves its settings when the
 * Product is saved in the WordPress Administration interface.
 *
 * @package CKWC
 * @author ConvertKit
 */
class CKWC_Admin_Product {

	/**
	 * Holds the WooCommerce Integration instance for this Plugin.
	 *
	 * @since   1.4.2
	 *
	 * @var     CKWC_Integration
	 */
	private $integration;

	/**
	 * Constructor
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		// Fetch integration.
		$this->integration = WP_CKWC_Integration();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'add_meta_boxes_product', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_product', array( $this, 'save_product' ) );

	}

	/**
	 * Enqueue Javascript for the WooCommerce Add / Edit Product screen.
	 *
	 * @since   1.4.3
	 */
	public function enqueue_scripts() {

		// Bail if we're not on the Add / Edit Product screen.
		if ( ! $this->is_edit_product_screen() ) {
			return;
		}

		// Enqueue Select2 JS.
		ckwc_select2_enqueue_scripts();

	}

	/**
	 * Enqueue CSS for the WooCommerce Add / Edit Product screen.
	 *
	 * @since   1.4.3
	 */
	public function enqueue_styles() {

		// Bail if we're not on the Add / Edit Product screen.
		if ( ! $this->is_edit_product_screen() ) {
			return;
		}

		// Enqueue CSS.
		wp_enqueue_style( 'ckwc-product', CKWC_PLUGIN_URL . '/resources/backend/css/product.css', array(), CKWC_PLUGIN_VERSION );

		// Enqueue Select2 CSS.
		ckwc_select2_enqueue_styles();

	}

	/**
	 * Adds a meta box on WooCommerce Products to choose a Form or Tag to subscribe the Customer to,
	 * overriding the Plugin default's Form / Tag.
	 *
	 * @since   1.0.0
	 */
	public function add_meta_boxes() {

		add_meta_box( 'ckwc', __( 'ConvertKit Integration', 'woocommerce-convertkit' ), array( $this, 'display_meta_box' ), null, 'side', 'default' );

	}

	/**
	 * Displays a meta box on WooCommerce Products to choose a Form or Tag to subscribe the Customer to,
	 * overriding the Plugin default's Form / Tag.
	 *
	 * @since   1.0.0
	 *
	 * @param   WP_Post $post   WooCommerce Product.
	 */
	public function display_meta_box( $post ) {

		// If the integration isn't enabled, show a message instead.
		if ( ! $this->integration->is_enabled() ) {
			$post_type = get_post_type_object( $post->post_type );
			require_once CKWC_PLUGIN_PATH . '/views/backend/product/disabled.php';
			return;
		}

		// Get Forms, Tags and Sequences.
		$forms     = new CKWC_Resource_Forms();
		$sequences = new CKWC_Resource_Sequences();
		$tags      = new CKWC_Resource_Tags();

		// Get current subscription setting and other settings to render the subscription dropdown field.
		$subscription = array(
			'id'        => 'ckwc_subscription',
			'class'     => 'ckwc-select2 widefat',
			'name'      => 'ckwc_subscription',
			'value'     => get_post_meta( $post->ID, 'ckwc_subscription', true ),
			'forms'     => $forms,
			'tags'      => $tags,
			'sequences' => $sequences,
		);

		// Load meta box view.
		require_once CKWC_PLUGIN_PATH . '/views/backend/product/meta-box.php';

	}

	/**
	 * Saves the WooCommerce Product's Form, Sequence or Tag to subscribe the Customer to when
	 * either editing a Product or using the Quick Edit functionality.
	 *
	 * @since   1.0.0
	 *
	 * @param   int $post_id    Product ID.
	 */
	public function save_product( $post_id ) {

		// If the integration isn't enabled, bail.
		if ( ! $this->integration->is_enabled() ) {
			return;
		}

		$data = stripslashes_deep( $_POST );

		// Bail if nonce is missing.
		if ( ! isset( $data['ckwc_nonce'] ) ) {
			return;
		}

		// Bail if nonce verification fails.
		if ( ! wp_verify_nonce( $data['ckwc_nonce'], 'ckwc' ) ) {
			return;
		}

		// Bail if no Form, Sequence or Tag option exists in POST data.
		if ( ! isset( $data['ckwc_subscription'] ) ) {
			return;
		}

		// Update Post Meta.
		update_post_meta( $post_id, 'ckwc_subscription', $data['ckwc_subscription'] );

	}

	/**
	 * Checks if the request is for viewing the WooCommerce Add / Edit Product screen.
	 *
	 * @since   1.4.3
	 *
	 * @return  bool
	 */
	private function is_edit_product_screen() {

		// Return false if we cannot reliably determine the current screen that is viewed,
		// due to WordPress' get_current_screen() function being unavailable.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		// Get screen.
		$screen = get_current_screen();

		// Return false if we're not on the Add / Edit Product screen.
		if ( $screen->id !== 'product' ) {
			return false;
		}

		return true;

	}

}
