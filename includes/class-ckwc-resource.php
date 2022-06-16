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
class CKWC_Resource extends ConvertKit_Resource {

	/**
	 * Constructor.
	 *
	 * @since   1.4.7
	 */
	public function __construct() {

		// Initialize the API if the API Key and Secret have been defined in the Plugin Settings.
		if ( WP_CKWC_Integration()->is_enabled() ) {
			$this->api = new CKWC_API(
				WP_CKWC_Integration()->get_option( 'api_key' ),
				WP_CKWC_Integration()->get_option( 'api_secret' ),
				WP_CKWC_Integration()->get_option_bool( 'debug' )
			);
		}

		// Call parent initialization function.
		parent::init();

	}

}
