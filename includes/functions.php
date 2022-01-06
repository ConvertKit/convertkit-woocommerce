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
