<?php
/**
 * Outputs a dropdown field comprising of all Forms, Tags and Sequences for the ConvertKit account.
 *
 * @package CKWC
 * @author ConvertKit
 */

?>

<select class="<?php echo esc_attr( $custom_field['class'] ); ?>" id="<?php echo esc_attr( $custom_field['id'] ); ?>" name="<?php echo esc_attr( $custom_field['name'] ); ?>">
	<option <?php selected( '', $custom_field['value'] ); ?> value="">
		<?php esc_html_e( '(Don\'t send or map)', 'woocommerce-convertkit' ); ?>
	</option>

	<?php
	if ( $custom_field['custom_fields']->exist() ) {
		foreach ( $custom_field['custom_fields']->get() as $api_custom_field ) {
			?>
			<option value="<?php echo esc_attr( $api_custom_field['key'] ); ?>"<?php selected( esc_attr( $api_custom_field['key'] ), $custom_field['value'] ); ?>><?php echo esc_html( $api_custom_field['label'] ); ?></option>
			<?php
		}
	}
	?>
</select>
