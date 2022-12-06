<?php
/**
 * Quick Edit view
 *
 * @package CKWC
 * @author ConvertKit
 */

?>
<div id="ckwc-quick-edit" class="ckwc-bulk-quick-edit">
	<h4><?php esc_html_e( 'ConvertKit for WooCommerce', 'woocommerce-convertkit' ); ?></h4>

	<div>
		<?php
		// Load subscription dropdown field.
		require CKWC_PLUGIN_PATH . '/views/backend/subscription-dropdown-field.php';
		?>
		<button class="ckwc-refresh-resources" class="button button-secondary" title="<?php esc_attr_e( 'Refresh sequences, forms and tags from ConvertKit account', 'woocommerce-convertkit' ); ?>" data-field="#<?php echo esc_attr( $subscription['id'] ); ?>">
			<span class="dashicons dashicons-update"></span>
		</button>
	</div>

	<?php
	wp_nonce_field( 'ckwc', 'ckwc_nonce' );
	?>
</div>
