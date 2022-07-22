<?php
/**
 * ConvertKit Admin Refresh Resources class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Registers scripts, which run when a Refresh button is clicked, to refresh resources
 * asynchronously whilst editing a WooCommerce Product.
 *
 * @package CKWC
 * @author ConvertKit
 */
class CKWC_Admin_Refresh_Resources {

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   1.4.8
	 */
	public function __construct() {

		add_action( 'wp_ajax_ckwc_admin_refresh_resources', array( $this, 'refresh_resources' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

	}

	/**
	 * Refreshes sequences, forms and tags from the API, returning them as a JSON string.
	 *
	 * @since   1.4.8
	 */
	public function refresh_resources() {

		// Check nonce.
		check_ajax_referer( 'ckwc_admin_refresh_resources', 'nonce' );

		// Define an array to store resources in.
		$resources = array();

		// Fetch forms.
		$forms              = new CKWC_Resource_Forms();
		$resources['forms'] = $forms->refresh();

		// Bail if an error occured.
		if ( is_wp_error( $resources['forms'] ) ) {
			wp_send_json_error( $resources['forms']->get_error_message() );
		}

		// Fetch sequences.
		$sequences              = new CKWC_Resource_Sequences();
		$resources['sequences'] = $sequences->refresh();

		// Bail if an error occured.
		if ( is_wp_error( $resources['sequences'] ) ) {
			wp_send_json_error( $resources['sequences']->get_error_message() );
		}

		// Fetch tags.
		$tags              = new CKWC_Resource_Tags();
		$resources['tags'] = $tags->refresh();

		// Bail if an error occured.
		if ( is_wp_error( $resources['tags'] ) ) {
			wp_send_json_error( $resources['tags']->get_error_message() );
		}

		// Return resources as a zero based sequential array, so that JS retains the order of resources.
		$resources['forms']     = array_values( $resources['forms'] );
		$resources['sequences'] = array_values( $resources['sequences'] );
		$resources['tags']      = array_values( $resources['tags'] );

		wp_send_json_success( $resources );

	}

	/**
	 * Enqueue JavaScript when editing a Page, Post, Custom Post Type or Category.
	 *
	 * @since   1.4.8
	 *
	 * @param   string $hook   Hook.
	 */
	public function enqueue_scripts( $hook ) {

		// Bail if we are not on an Edit Product screen.
		if ( $hook !== 'edit.php' && $hook !== 'post-new.php' && $hook !== 'post.php' ) {
			return;
		}

		// Get integration.
		$integration = WP_CKWC_Integration();

		// Bail if no API keys are defined.
		if ( ! $integration->is_enabled() ) {
			return;
		}

		// Enqueue JS to perform AJAX request to refresh resources.
		wp_enqueue_script( 'ckwc-admin-refresh-resources', CKWC_PLUGIN_URL . 'resources/backend/js/refresh-resources.js', array( 'jquery' ), CKWC_PLUGIN_VERSION, true );
		wp_localize_script(
			'ckwc-admin-refresh-resources',
			'ckwc_admin_refresh_resources',
			array(
				'action'  => 'ckwc_admin_refresh_resources',
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'debug'   => $integration->get_option_bool( 'debug' ),
				'nonce'   => wp_create_nonce( 'ckwc_admin_refresh_resources' ),
			)
		);

	}

}
