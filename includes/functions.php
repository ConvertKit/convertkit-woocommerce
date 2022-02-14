<?php
/**
 * ConvertKit for WooCommerce general plugin functions.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Helper method to return the Plugin Settings Link
 *
 * @since   1.4.2
 *
 * @param   array $query_args     Optional Query Args.
 * @return  string                  Settings Link
 */
function ckwc_get_settings_link( $query_args = array() ) {

	$query_args = array_merge(
		$query_args,
		array(
			'page'    => 'wc-settings',
			'tab'     => 'integration',
			'section' => 'ckwc',
		)
	);

	return add_query_arg( $query_args, admin_url( 'admin.php' ) );

}

/**
 * Helper method to return the URL the user needs to visit to sign in to their ConvertKit account.
 *
 * @since   1.4.2
 *
 * @return  string  ConvertKit Login URL.
 */
function ckwc_get_sign_in_url() {

	return 'https://app.convertkit.com/?utm_source=wordpress&utm_content=convertkit-for-woocommerce';

}

/**
 * Helper method to return the URL the user needs to visit to sign up for a ConvertKit account.
 *
 * @since   1.4.2
 *
 * @return  string  ConvertKit Signup URL.
 */
function ckwc_get_signup_url() {

	return 'https://app.convertkit.com/users/signup?utm_source=wordpress&utm_content=convertkit-for-woocommerce';

}

/**
 * Helper method to return the URL the user needs to visit on the ConvertKit app to obtain their API Key and Secret.
 *
 * @since   1.4.2
 *
 * @return  string  ConvertKit App URL.
 */
function ckwc_get_api_key_url() {

	return 'https://app.convertkit.com/account_settings/advanced_settings/?utm_source=wordpress&utm_content=convertkit-for-woocommerce';

}

/**
 * Helper method to enqueue Select2 scripts for use within the ConvertKit Plugin.
 *
 * @since   1.4.3
 */
function ckwc_select2_enqueue_scripts() {

	wp_enqueue_script( 'ckwc-select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), CKWC_PLUGIN_VERSION, false );
	wp_enqueue_script( 'ckwc-admin-select2', CKWC_PLUGIN_URL . '/resources/backend/js/select2.js', array( 'ckwc-select2' ), CKWC_PLUGIN_VERSION, false );

}

/**
 * Helper method to enqueue Select2 stylesheets for use within the ConvertKit Plugin.
 *
 * @since   1.4.3
 */
function ckwc_select2_enqueue_styles() {

	wp_enqueue_style( 'ckwc-select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', false, CKWC_PLUGIN_VERSION );
	wp_enqueue_style( 'ckwc-admin-select2', CKWC_PLUGIN_URL . '/resources/backend/css/select2.css', false, CKWC_PLUGIN_VERSION );

}
