<?php
/**
 * Bulk Edit view
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>
<div id="ckwc-bulk-edit" style="display:none;">
	<h4><?php esc_html_e( 'ConvertKit for WooCommerce', 'woocommerce-convertkit' ); ?></h4>

	<?php
	// Load subscription dropdown field.
	require CKWC_PLUGIN_PATH . '/views/backend/subscription-dropdown-field.php';

	wp_nonce_field( 'ckwc', 'ckwc_nonce' );
	?>
</div>

