<?php
/**
 * ConvertKit Opt In Block class.
 *
 * @package CKWC
 * @author ConvertKit
 */

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Registers an opt in block for the WooCommerce Checkout Block.
 *
 * @package CKWC
 * @author ConvertKit
 */
class CKWC_Opt_In_Block_Integration implements IntegrationInterface {

	/**
	 * Holds the WooCommerce Integration instance for this Plugin.
	 *
	 * @since   1.7.1
	 *
	 * @var     CKWC_Integration
	 */
	private $integration;

    /**
	 * The name of the integration.
	 * 
	 * @since 	1.7.1
	 *
	 * @return string
	 */
	public function get_name() {

		return 'ckwc_opt_in';

	}

	/**
	 * Register frontend and backend scripts on block registration.
	 * 
	 * @since 	1.7.1
	 */
	public function initialize() {

		// Fetch integration.
		$this->integration = WP_CKWC_Integration();

		$this->register_frontend_scripts();
		$this->register_editor_scripts();
		$this->register();
		$this->extend_store_api();

	}

	public function register_frontend_scripts() {

		wp_register_script(
			'ckwc-opt-in-block-frontend',
			CKWC_PLUGIN_URL . 'resources/frontend/js/opt-in-block.js',
			array( 'jquery' ),
			CKWC_PLUGIN_VERSION,
			true
		);

		// Include settings from CKWC_Integration as an object for the script,
		// as these determine if the checkbox should be available, and if so its
		// default checked state and label.
		// Typically these would be presented as options to the user in the block
		// editor, however this plugin has historically stored the settings
		// at WooCommerce > Settings > Integrations > ConvertKit, so we need
		// to honor those settings.
		wp_localize_script( 'ckwc-opt-in-block-frontend', 'ckwc_integration', array(
			'enabled' => $this->integration->is_enabled(),
			'display_opt_in' => $this->integration->get_option_bool( 'display_opt_in' ), 
			'opt_in_label' => $this->integration->get_option( 'opt_in_label' ),
			'opt_in_status' => $this->integration->get_option( 'opt_in_status' ),
		) );

	}

	/**
	 * Register JS to register the block in the block editor.
	 * 
	 * @since 	1.7.1
	 */
	public function register_editor_scripts() {
	
		wp_register_script(
			'ckwc-opt-in-block',
			CKWC_PLUGIN_URL . 'resources/backend/js/opt-in-block.js',
			array( 'jquery' ),
			CKWC_PLUGIN_VERSION,
			true
		);

		// Include settings from CKWC_Integration as an object for the script,
		// as these determine if the checkbox should be available, and if so its
		// default checked state and label.
		// Typically these would be presented as options to the user in the block
		// editor, however this plugin has historically stored the settings
		// at WooCommerce > Settings > Integrations > ConvertKit, so we need
		// to honor those settings.
		wp_localize_script( 'ckwc-opt-in-block', 'ckwc_integration', array(
			'enabled' => $this->integration->is_enabled(),
			'display_opt_in' => $this->integration->get_option_bool( 'display_opt_in' ), 
			'opt_in_label' => $this->integration->get_option( 'opt_in_label' ),
			'opt_in_status' => $this->integration->get_option( 'opt_in_status' ),
		) );
		
	}

	/**
	 * Registers the block.
	 * 
	 * @since 	1.7.1
	 */
	public function register() {

		register_block_type( CKWC_PLUGIN_PATH . '/includes/blocks/opt-in', array(
			'editor_script' => 'ckwc-opt-in-block',
		) );

	}

	/**
	 * Registers this block's data in the Store API, so posted data
	 * is saved.
	 * 
	 * @since 	1.7.1
	 */
	public function extend_store_api() {

		// Bail if function not available.
		if ( ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
			return;
		}

        woocommerce_store_api_register_endpoint_data(
            array(
                'endpoint'        => \Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema::IDENTIFIER,
                'namespace'       => $this->get_name(),
                'schema_callback' => array( $this, 'schema' ),
                'schema_type'     => ARRAY_A,
            )
        );

	}

	/**
	 * Defines the data schema for the opt in block.
	 * 
	 * @since 	1.7.1
	 * 
	 * @return 	array
	 */
	public function schema() {

		return array(
			$this->get_name() => array(
				'description' => $this->integration->get_option( 'opt_in_label' ),
				'type' => 'boolean',
				'context' => array(),
			),
		);

	}
	
	/**
	 * Returns scripts to enqueue in the frontend site.
	 *
	 * @since 	1.7.1
	 *
	 * @return  array
	 */
	public function get_script_handles() {

		return array( 'ckwc-opt-in-block-frontend' );

	}

	/**
	 * Returns scripts to enqueue in the block editor.
	 * 
	 * @since 	1.7.1
	 *
	 * @return  array
	 */
	public function get_editor_script_handles() {

		return array( 'ckwc-opt-in-block' );

	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		$data = array(
			'optinDefaultText' => __( 'I want to receive updates about products and promotions.', 'newsletter-test' ),
		);

		return $data;
	}

}