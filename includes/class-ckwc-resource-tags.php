<?php
/**
 * ConvertKit Tags Resource class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Reads ConvertKit Tags from the options table, and refreshes
 * ConvertKit Tags data stored locally from the API.
 *
 * @since   1.4.2
 */
class CKWC_Resource_Tags extends CKWC_Resource {

	/**
	 * Holds the Settings Key that stores site wide ConvertKit settings
	 *
	 * @var     string
	 */
	public $settings_name = 'ckwc_tags';

	/**
	 * The type of resource
	 *
	 * @var     string
	 */
	public $type = 'tags';

}
