<?php
/**
 * Plugin activation, update and deactivation class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Runs any steps required on plugin activation, update and deactivation.
 *
 * @since 1.8.0
 */
class CKWC_Setup {

	/**
	 * Runs routines when the Plugin version has been updated.
	 *
	 * @since   1.8.0
	 */
	public function update() {

		// Get installed Plugin version.
		$current_version = get_option( 'ckwc_version' );

		// If the version number matches the plugin version, no update routines
		// need to run.
		if ( $current_version === CKWC_PLUGIN_VERSION ) {
			return;
		}

		/**
		 * 1.8.0: Get Access token for API version 4.0 using a v3 API Key and Secret.
		 */
		if ( ! $current_version || version_compare( $current_version, '1.8.0', '<' ) ) {
			$this->maybe_get_access_token_by_api_key_and_secret();
		}

		// Update the installed version number in the options table.
		update_option( 'ckwc_version', CKWC_PLUGIN_VERSION );

	}

	/**
	 * 1.8.0: Fetch an Access Token, Refresh Token and Expiry for v4 API use
	 * based on the Plugin setting's v3 API Key and Secret.
	 *
	 * @since   1.8.0
	 */
	private function maybe_get_access_token_by_api_key_and_secret() {

		// Bail if ConverKit for WooCommerce not active.
		if ( ! function_exists( 'WP_CKWC_Integration' ) ) {
			return;
		}

		// Load integration.
		$integration = WP_CKWC_Integration();

		// Bail if ConverKit for WooCommerce not active.
		if ( ! $integration ) {
			return;
		}

		// Bail if an Access Token exists; we don't need to fetch another one.
		if ( $integration->has_access_token() ) {
			return;
		}

		// Bail if no API Key or Secret.
		if ( ! $integration->has_api_key() ) {
			return;
		}
		if ( ! $integration->has_api_secret() ) {
			return;
		}

		// Get Access Token by API Key and Secret.
		$api    = new CKWC_API( CKWC_OAUTH_CLIENT_ID, CKWC_OAUTH_CLIENT_REDIRECT_URI );
		$result = $api->get_access_token_by_api_key_and_secret(
			$integration->get_api_key(),
			$integration->get_api_secret()
		);

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return;
		}

		// Store the new credentials.
		$integration->update_option( 'access_token', $result['oauth']['access_token'] );
		$integration->update_option( 'refresh_token', $result['oauth']['refresh_token'] );
		$integration->update_option( 'token_expires', $result['oauth']['expires_at'] );

	}

}
