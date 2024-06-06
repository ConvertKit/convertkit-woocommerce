<?php
/**
 * ConvertKit Resource class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Abstract class defining variables and functions for a ConvertKit API Resource
 * (forms, sequences, tags).
 *
 * @since   1.4.2
 */
class CKWC_Resource extends ConvertKit_Resource_V4 {

	/**
	 * Constructor.
	 *
	 * @since   1.4.7
	 */
	public function __construct() {

		// Initialize the API if the integration is connected to ConvertKit and has been enabled in the Plugin Settings.
		if ( WP_CKWC_Integration()->is_enabled() ) {
			$this->api = new CKWC_API(
				CKWC_OAUTH_CLIENT_ID,
				CKWC_OAUTH_CLIENT_REDIRECT_URI,
				WP_CKWC_Integration()->get_option( 'access_token' ),
				WP_CKWC_Integration()->get_option( 'refresh_token' ),
				WP_CKWC_Integration()->get_option_bool( 'debug' )
			);
		}

		// Call parent initialization function.
		parent::init();

	}

}
