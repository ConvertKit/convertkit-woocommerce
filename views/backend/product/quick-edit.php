<?php
/**
 * Quick Edit view
 *
 * @package CKWC
 * @author ConvertKit
 */

?>
<div class="ckwc-quick-edit" style="display:none;">
	<h4><?php esc_html_e( 'ConvertKit for WooCommerce', 'woocommerce-convertkit' ); ?></h4>

	<?php
	// Load subscription dropdown field.
	require_once CKWC_PLUGIN_PATH . '/views/backend/subscription-dropdown-field.php';

	wp_nonce_field( 'ckwc', 'ckwc_nonce' );
	?>
</div>
