<?php
/**
 * Outputs a dropdown field comprising of all Forms, Tags and Sequences for the ConvertKit account.
 *
 * @package CKWC
 * @author ConvertKit
 */

// Load subscription dropdown field.
require_once CKWC_PLUGIN_PATH . '/views/backend/subscription-dropdown-field.php';
?>

<p class="description">
	<?php esc_html_e( 'The ConvertKit form, tag or sequence to subscribe customers to who purchase this product.', 'woocommerce-convertkit' ); ?>
</p>

<?php
wp_nonce_field( 'ckwc', 'ckwc_nonce' );
