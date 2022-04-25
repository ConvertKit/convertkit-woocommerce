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
	 * The number of seconds resources are valid, before they should be
	 * fetched again from the API.
	 *
	 * @var     int
	 *
	 * @since   1.4.7
	 */
	public $cache_duration = YEAR_IN_SECONDS;

	/**
	 * Holds the resources from the ConvertKit API
	 *
	 * @var     WP_Error|array
	 */
	public $resources = array();

	/**
	 * Timestamp for when the resources stored in the option database table
	 * were last queried from the API.
	 *
	 * @since   1.4.7
	 *
	 * @var     int
	 */
	public $last_queried = 0;

	/**
	 * Constructor. Populate the resources array of e.g. forms, landing pages or tags.
	 *
	 * @since   1.4.2
	 */
	public function __construct() {

		$this->init();

	}

	/**
	 * Initialization routine. Populate the resources array of e.g. forms, landing pages or tags,
	 * depending on whether resources are already cached, if the resources have expired etc.
	 *
	 * @since   1.4.7
	 */
	public function init() {

		// Get last query time and existing resources.
		$this->last_queried = get_option( $this->settings_name . '_last_queried' );
		$this->resources    = get_option( $this->settings_name );

		// If no last query time exists, refresh the resources now, which will set
		// a last query time.  This handles upgrades from < 1.9.7.4 where resources
		// would never expire.
		if ( ! $this->last_queried ) {
			$this->refresh();
			return;
		}

		// If no resources exist, refresh them now.
		if ( ! $this->resources ) {
			$this->refresh();
			return;
		}

		// If the resources have expired, refresh them now.
		if ( time() > ( $this->last_queried + $this->cache_duration ) ) {
			$this->refresh();
			return;
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

		if ( $this->resources === false ) { // @phpstan-ignore-line.
			return false;
		}

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
	 * @return  bool|WP_Error|array
	 */
	public function refresh() {

		// Bail if the API Key and Secret hasn't been defined in the Plugin Settings.
		if ( ! WP_CKWC_Integration()->is_enabled() ) {
			return false;
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
				break;
		}

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return $results;
		}

		// Define last query time now.
		$last_queried = time();

		// Store resources and their last query timestamp in the options table.
		// We don't use WordPress' Transients API (i.e. auto expiring options), because they're prone to being
		// flushed by some third party "optimization" Plugins. They're also not guaranteed to remain in the options
		// table for the amount of time specified; any expiry is a maximum, not a minimum.
		// We don't want to keep querying the ConvertKit API for a list of e.g. forms, tags that rarely change as
		// a result of transients not being honored, so storing them as options with a separate, persistent expiry
		// value is more reliable here.
		update_option( $this->settings_name, $results );
		update_option( $this->settings_name . '_last_queried', $last_queried );

		// Store resources and last queried time in class variables.
		$this->resources    = $results;
		$this->last_queried = $last_queried;

		// Return resources.
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
