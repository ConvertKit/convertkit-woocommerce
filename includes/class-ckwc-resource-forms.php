<?php
/**
 * ConvertKit Forms Resource class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Reads ConvertKit Forms from the options table, and refreshes
 * ConvertKit Forms data stored locally from the API.
 *
 * @since   1.4.2
 */
class CKWC_Resource_Forms extends CKWC_Resource {

	/**
	 * Holds the Settings Key that stores site wide ConvertKit settings
	 *
	 * @var     string
	 */
	public $settings_name = 'ckwc_forms';

	/**
	 * The type of resource
	 *
	 * @var     string
	 */
	public $type = 'forms';

	/**
	 * Determines if the given Form ID is a legacy Form or Landing Page.
	 *
	 * @since   1.8.0
	 *
	 * @param   int $id     Form or Landing Page ID.
	 */
	public function is_legacy( $id ) {

		// Get Form.
		$form = $this->get_by_id( (int) $id );

		// Return false if no Form exists.
		if ( ! $form ) {
			return false;
		}

		// If the `format` key exists, this is not a legacy Form.
		if ( array_key_exists( 'format', $form ) ) {
			return false;
		}

		return true;

	}

}
