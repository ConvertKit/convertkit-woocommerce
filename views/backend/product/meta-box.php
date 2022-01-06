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
	<?php esc_html_e( 'The ConvertKit Form, Tag or Sequence to subscribe Customers to who purchase this Product.', 'woocommerce-convertkit' ); ?>
</p>

<?php
wp_nonce_field( 'ckwc', 'ckwc_nonce' );
