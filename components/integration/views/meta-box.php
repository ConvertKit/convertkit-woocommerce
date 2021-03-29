<?php if ( 'yes' !== $this->enabled || empty( $this->api_key ) ) : ?>
	<p>
		<strong><?php esc_html_e( 'Important:', 'woocommerce-convertkit' ); ?></strong>
		<?php esc_html_e( 'The ConvertKit integration for WooCommerce is currently disabled. Customers will not be subscribed when purchasing.', 'woocommerce-convertkit' ); ?>
	</p>
<?php endif; ?>

<?php if ( $options ) : ?>
	<select class="widefat" id="ckwc_subscription" name="ckwc_subscription">
		<option <?php selected( '', $subscription ); ?> value=""><?php esc_html_e( 'Select a subscription option...', 'woocommerce-convertkit' ); ?></option>

		<?php
		foreach ( $options as $option_group ) :
			if ( empty( $option_group['options'] ) ) {
				continue;
			}
			?>

			<optgroup label="<?php echo esc_attr( $option_group['name'] ); ?>">
				<?php
				foreach ( $option_group['options'] as $id => $name ) :
					$value = "{$option_group['key']}:{$id}";
					?>
					<option <?php selected( $value, $subscription ); ?> value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</optgroup>

		<?php endforeach; ?>
	</select>
<?php else : ?>
	<p>
		<?php esc_html_e( 'Please enter a valid ConvertKit API Key on the ConvertKit Integration Settings page.', 'woocommerce-convertkit' ); ?>
	</p>
<?php endif; ?>

<?php wp_nonce_field( 'ckwc', 'ckwc_nonce' ); ?>
