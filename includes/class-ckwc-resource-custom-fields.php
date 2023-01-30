<?php
/**
 * ConvertKit Custom Fields Resource class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Reads ConvertKit Custom Fields from the options table, and refreshes
 * ConvertKit Custom Fields data stored locally from the API.
 *
 * @since   1.4.3
 */
class CKWC_Resource_Custom_Fields extends CKWC_Resource {

	/**
	 * Holds the Settings Key that stores site wide ConvertKit settings
	 *
	 * @var     string
	 */
	public $settings_name = 'ckwc_custom_fields';

	/**
	 * The type of resource
	 *
	 * @var     string
	 */
	public $type = 'custom_fields';

	/**
	 * The key to use when alphabetically sorting resources.
	 *
	 * @since   1.5.7
	 *
	 * @var     string
	 */
	public $order_by = 'label';

}
