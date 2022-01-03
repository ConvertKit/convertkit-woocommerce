<?php
/**
 * ConvertKit Admin Product class.
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
class CKWC_Admin_Product {

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

		add_action( 'add_meta_boxes_product', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_product', array( $this, 'save_product' ) );
		
	}

	/**
	 * Adds a meta box on WooCommerce Products to choose a Form or Tag to subscribe the Customer to,
	 * overriding the Plugin default's Form / Tag.
	 * 
	 * @since 	1.0.0
	 * 
	 * @param 	WP_Post 	$post 	WooCommerce Product
	 */
	public function add_meta_boxes( $post ) {

		add_meta_box( 'ckwc', __( 'ConvertKit Integration', 'woocommerce-convertkit' ), array( $this, 'display_meta_box' ), null, 'side', 'default' );
	
	}

	/**
	 * Displays a meta box on WooCommerce Products to choose a Form or Tag to subscribe the Customer to,
	 * overriding the Plugin default's Form / Tag.
	 * 
	 * @since 	1.0.0
	 * 
	 * @param 	WP_Post 	$post 	WooCommerce Product
	 */
	public function display_meta_box( $post ) {

		$subscription = get_post_meta( $post->ID, 'ckwc_subscription', true );
		$options      = ckwc_get_subscription_options();

		require_once CKWC_PLUGIN_PATH . '/resources/backend/product/meta-box.php';

	}

	/**
	 * Saves the WooCommerce Product's Form or Tag to subscribe the Customer to when a
	 * Product is edited, overriding the Plugin default's Form / Tag.
	 * 
	 * @since 	1.0.0
	 * 
	 * @param  	int 	$post_id 	Product ID
	 */
	public function save_product( $post_id ) {

		$data = stripslashes_deep( $_POST ); // WPCS: input var okay. CSRF ok.

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