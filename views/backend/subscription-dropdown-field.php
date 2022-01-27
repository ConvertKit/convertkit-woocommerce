<?php
/**
 * Outputs a dropdown field comprising of all Forms, Tags and Sequences for the ConvertKit account.
 *
 * @package CKWC
 * @author ConvertKit
 */

?>

<select class="<?php echo esc_attr( $subscription['class'] ); ?>" id="<?php echo esc_attr( $subscription['id'] ); ?>" name="<?php echo esc_attr( $subscription['name'] ); ?>">
	<option <?php selected( '', $subscription['value'] ); ?> value="">
		<?php esc_html_e( 'Select a subscription option...', 'woocommerce-convertkit' ); ?>
	</option>

	<?php
	if ( ! is_wp_error( $subscription['sequences']->get() ) ) {
		?>
		<optgroup label="<?php esc_attr_e( 'Sequences', 'woocommerce-convertkit' ); ?>">
			<?php
			foreach ( $subscription['sequences']->get() as $sequence ) {
				// 'course:' is deliberate for backward compat. functionality when Sequences used to be called Courses.
				?>
				<option value="course:<?php echo esc_attr( $sequence['id'] ); ?>"<?php selected( 'course:' . esc_attr( $sequence['id'] ), $subscription['value'] ); ?>><?php echo esc_html( $sequence['name'] ); ?></option>
				<?php
			}
			?>
		</optgroup>
		<?php
	}

	if ( ! is_wp_error( $subscription['forms']->get() ) ) {
		?>
		<optgroup label="<?php esc_attr_e( 'Forms', 'woocommerce-convertkit' ); ?>">
			<?php
			foreach ( $subscription['forms']->get() as $form ) {
				?>
				<option value="form:<?php echo esc_attr( $form['id'] ); ?>"<?php selected( 'form:' . esc_attr( $form['id'] ), $subscription['value'] ); ?>><?php echo esc_html( $form['name'] ); ?></option>
				<?php
			}
			?>
		</optgroup>
		<?php
	}

	if ( ! is_wp_error( $subscription['tags']->get() ) ) {
		?>
		<optgroup label="<?php esc_attr_e( 'Tags', 'woocommerce-convertkit' ); ?>">
			<?php
			foreach ( $subscription['tags']->get() as $tag ) { // phpcs:ignore
				?>
				<option value="tag:<?php echo esc_attr( $tag['id'] ); ?>"<?php selected( 'tag:' . esc_attr( $tag['id'] ), $subscription['value'] ); ?>><?php echo esc_html( $tag['name'] ); ?></option>
				<?php
			}
			?>
		</optgroup>
		<?php
	}
	?>
</select>
