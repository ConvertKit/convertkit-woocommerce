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
	 * @var     WC_Integration
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

		add_action( 'add_meta_boxes_product', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_product', array( $this, 'save_product' ) );

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

		// Get Forms, Tags, Sequences, current subscription setting and other
		// settings to render the subscription dropdown field.
		$api          = new CKWC_API(
			$this->integration->get_option( 'api_key' ),
			$this->integration->get_option( 'api_secret' ),
			$this->integration->get_option_bool( 'debug' )
		);
		$subscription = array(
			'id'        => 'ckwc_subscription',
			'class'     => 'widefat',
			'name'      => 'ckwc_subscription',
			'value'     => get_post_meta( $post->ID, 'ckwc_subscription', true ),
			'forms'     => $api->get_forms(),
			'tags'      => $api->get_tags(),
			'sequences' => $api->get_sequences(),
		);

		// Load meta box view.
		require_once CKWC_PLUGIN_PATH . '/views/backend/product/meta-box.php';

	}

	/**
	 * Saves the WooCommerce Product's Form or Tag to subscribe the Customer to when a
	 * Product is edited, overriding the Plugin default's Form / Tag.
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

		$data = stripslashes_deep( $_POST ); // phpcs:ignore

		// Bail if nonce is missing.
		if ( ! isset( $data['ckwc_nonce'] ) ) {
			return;
		}

		// Bail if nonce verification fails.
		if ( ! wp_verify_nonce( $data['ckwc_nonce'], 'ckwc' ) ) {
			return;
		}

		// Bail if no Form / Tag option exists in POST data.
		if ( ! isset( $data['ckwc_subscription'] ) ) {
			return;
		}

		// Update Post Meta.
		update_post_meta( $post_id, 'ckwc_subscription', $data['ckwc_subscription'] );

	}

}
