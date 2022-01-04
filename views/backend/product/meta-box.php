<?php
/**
 * Outputs a dropdown field comprising of all Forms and Tags for the ConvertKit account.
 *
 * @package CKWC
 * @author ConvertKit
 */

?>

<select class="widefat" id="ckwc_subscription" name="ckwc_subscription">
	<option <?php selected( '', $subscription ); ?> value="">
		<?php esc_html_e( 'Select a subscription option...', 'woocommerce-convertkit' ); ?>
	</option>
	<?php
	// Forms.
	if ( ! is_wp_error( $forms ) ) {
		?>
		<optgroup label="<?php _e( 'Forms', 'woocommerce-convertkit' ); ?>">
			<?php 
			foreach ( $forms as $form ) {
				?>
				<option value="form:<?php echo esc_attr( $form['id'] ); ?>"<?php selected( 'form:' . esc_attr( $form['id'] ), $subscription ); ?>><?php echo $form['name']; ?></option>
				<?php
			}
			?>
		</optgroup>
		<?php
	}

	// Tags.
	if ( ! is_wp_error( $tags ) ) {
		?>
		<optgroup label="<?php _e( 'Tags', 'woocommerce-convertkit' ); ?>">
			<?php 
			foreach ( $tags as $tag ) {
				?>
				<option value="tag:<?php echo esc_attr( $tag['id'] ); ?>"<?php selected( 'tag:' . esc_attr( $tag['id'] ), $subscription ); ?>><?php echo $tag['name']; ?></option>
				<?php
			}
			?>
		</optgroup>
		<?php
	}

	// Sequences (previously called Courses).
	if ( ! is_wp_error( $sequences ) ) {
		?>
		<optgroup label="<?php _e( 'Sequences', 'woocommerce-convertkit' ); ?>">
			<?php 
			foreach ( $sequences as $sequence ) {
				// 'course:' is deliberate for backward compat. functionality when Sequences used to be called Courses.
				?>
				<option value="course:<?php echo esc_attr( $tag['id'] ); ?>"<?php selected( 'course:' . esc_attr( $sequence['id'] ), $subscription ); ?>><?php echo $sequence['name']; ?></option>
				<?php
			}
			?>
		</optgroup>
		<?php
	}
	?>
</select>

<?php 
wp_nonce_field( 'ckwc', 'ckwc_nonce' );
