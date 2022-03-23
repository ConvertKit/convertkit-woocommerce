<?php
/**
 * Outputs the Sync Past Orders screen.
 *
 * @package CKWC
 * @author ConvertKit
 */

?>

<h2><?php esc_html_e( 'ConvertKit', 'woocommerce-convertkit' ); ?></h2>

<div>
	<p>
		<?php esc_html_e( 'Do not navigate away from this page until the process is completed, otherwise complete purchase data will not be sent to ConvertKit. You will be notified via this page when the process is completed.', 'woocommerce-convertkit' ); ?>
	</p>

	<!-- Progress Bar -->
	<div id="progress-bar"></div>
	<div id="progress">
		<span id="progress-number">0</span>
		<span> / <?php echo count( $this->unsynced_order_ids ); ?></span>
	</div>

	<!-- Log -->
	<div id="log">
		<ul></ul>
	</div>

	<p>
		<!-- Cancel Button -->
		<a href="<?php echo esc_attr( $return_url ); ?>" class="button button-secondary cancel">
			<?php esc_html_e( 'Cancel Sync', 'woocommerce-convertkit' ); ?>
		</a>

		<!-- Return Button (display when routine finishes) -->
		<a href="<?php echo esc_attr( $return_url ); ?>" class="button button-secondary return">
			<?php esc_html_e( 'Return to settings', 'woocommerce-convertkit' ); ?>
		</a>
	</p>
</div>
