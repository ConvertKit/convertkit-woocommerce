<?php
/**
 * ConvertKit Sequences Resource class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Reads ConvertKit Sequences from the options table, and refreshes
 * ConvertKit Sequences data stored locally from the API.
 *
 * @since   1.4.2
 */
class CKWC_Resource_Sequences extends CKWC_Resource {

	/**
	 * Holds the Settings Key that stores site wide ConvertKit settings
	 *
	 * @var     string
	 */
	public $settings_name = 'ckwc_sequences';

	/**
	 * The type of resource
	 *
	 * @var     string
	 */
	public $type = 'sequences';

}
