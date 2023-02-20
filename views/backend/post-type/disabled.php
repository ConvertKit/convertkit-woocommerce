<?php
/**
 * Outputs a message in the metabox when the integration is disabled.
 *
 * @package CKWC
 * @author ConvertKit
 */

?>

<p>
	<?php
	echo sprintf(
		/* translators: %1$s: Post Type Singular Name, %2$s: Link to Integration Settings */
		esc_html__( 'To configure the ConvertKit form, tag or sequence to subscribe customers to who use this %1$s, %2$s and enter a valid API Key and API Secret.', 'woocommerce-convertkit' ),
		esc_attr( $post_type->labels->singular_name ),
		'<a href="' . esc_attr( admin_url( 'admin.php?page=wc-settings&tab=integration&section=ckwc' ) ) . '">' . esc_html__( 'enable the ConvertKit WooCommerce integration', 'woocommerce-convertkit' ) . '</a>'
	);
	?>
</p>
