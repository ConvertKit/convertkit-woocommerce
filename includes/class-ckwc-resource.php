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
class CKWC_Resource {

	/**
	 * Holds the Settings Key that stores site wide ConvertKit settings
	 *
	 * @var     string
	 */
	public $settings_name = '';

	/**
	 * The type of resource
	 *
	 * @var     string
	 */
	public $type = '';

	/**
	 * Holds the resources from the ConvertKit API
	 *
	 * @var     WP_Error|array
	 */
	public $resources = array();

	/**
	 * Constructor. Populate the resources array of e.g. forms, landing pages or tags.
	 *
	 * @since   1.4.2
	 */
	public function __construct() {

		// Get resources from options.
		$resources = get_option( $this->settings_name );

		// If resources exist in the options table, use them.
		if ( is_array( $resources ) ) {
			$this->resources = $resources;
		} else {
			// No options exist in the options table. Fetch them from the API, storing
			// them in the options table.
			$this->resources = $this->refresh();
		}

	}

	/**
	 * Returns all resources.
	 *
	 * @since   1.4.2
	 *
	 * @return  array
	 */
	public function get() {

		return $this->resources;

	}

	/**
	 * Returns an individual resource by its ID.
	 *
	 * @since   1.4.2
	 *
	 * @param   int $id     Resource ID (Form, Tag, Sequence).
	 * @return  mixed           bool | array
	 */
	public function get_by_id( $id ) {

		foreach ( $this->get() as $resource ) {
			// If this resource's ID matches the ID we're looking for, return it.
			if ( $resource['id'] == $id ) { // phpcs:ignore
				return $resource;
			}
		}

		return false;

	}

	/**
	 * Returns whether any resources exist in the options table.
	 *
	 * @since   1.4.2
	 *
	 * @return  bool
	 */
	public function exist() {

		if ( is_wp_error( $this->resources ) ) {
			return false;
		}

		if ( is_null( $this->resources ) ) {
			return false;
		}

		return ( count( $this->resources ) ? true : false );

	}

	/**
	 * Fetches resources (forms, landing pages or tags) from the API, storing them in the options table.
	 *
	 * @since   1.4.2
	 *
	 * @return  mixed           WP_Error | array
	 */
	public function refresh() {

		// Bail if the API Key and Secret hasn't been defined in the Plugin Settings.
		if ( ! WP_CKWC_Integration()->is_enabled() ) {
			return;
		}

		// Setup the API.
		$api = new CKWC_API(
			WP_CKWC_Integration()->get_option( 'api_key' ),
			WP_CKWC_Integration()->get_option( 'api_secret' ),
			WP_CKWC_Integration()->get_option_bool( 'debug' )
		);

		// Fetch resources.
		switch ( $this->type ) {
			case 'forms':
				$results = $api->get_forms();
				break;

			case 'sequences':
				$results = $api->get_sequences();
				break;

			case 'tags':
				$results = $api->get_tags();
				break;

			case 'custom_fields':
				$results = $api->get_custom_fields();
				break;

			default:
				$results = new WP_Error(
					'convertkit_for_woocommerce_resource_refresh_error',
					sprintf(
						/* translators: Resource Type */
						__( 'Resource type %s is not supported in CKWC_Resource class.', 'woocommerce-convertkit' ),
						$this->type
					)
				);
		}

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return $results;
		}

		// Update options table data.
		update_option( $this->settings_name, $results );

		// Store in resource class.
		$this->resources = $results;

		return $results;

	}

	/**
	 * Deletes resources (forms, landing pages or tags) from the options table.
	 *
	 * @since   1.4.2
	 */
	public function delete() {

		delete_option( $this->settings_name );

	}

}
