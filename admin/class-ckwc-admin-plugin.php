<?php
/**
 * ConvertKit Admin Plugin class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Registers a link on the Plugin's entry in the Plugins screen, linking to the
 * integration settings interface.
 *
 * @package CKWC
 * @author ConvertKit
 */
class CKWC_Admin_Plugin {

	/**
	 * Constructor
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		add_filter( 'plugin_action_links_' . CKWC_PLUGIN_FILE, array( $this, 'plugin_links' ) );

	}

	/**
	 * Registers a link on the Plugin's entry in the Plugins screen, linking to the
	 * integration settings interface.
	 *
	 * @since   1.0.0
	 *
	 * @param   array $links  Plugin Links.
	 * @return  array           Plugin Links
	 */
	public function plugin_links( $links ) {

		return array_merge(
			array(
				'<a href="' . ckwc_get_settings_link() . '">' . __( 'Settings', 'woocommerce-convertkit' ) . '</a>',
			),
			$links
		);

	}

}
