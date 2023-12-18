<?php
/**
 * ConvertKit WP-CLI Sync Past Orders class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Registers a WP-CLI command to sync past orders with ConvertKit.
 *
 * @package CKWC
 * @author ConvertKit
 */
class CKWC_CLI_Sync_Past_Orders {

	/**
	 * Holds the WooCommerce Integration instance for this Plugin.
	 *
	 * @since   1.7.1
	 *
	 * @var     CKWC_Integration
	 */
	private $integration;

	/**
	 * Sync past orders with ConvertKit Purchase Data.
	 *
	 * @since   1.7.1
	 *
	 * @param   array $args           @TODO.
	 * @param   array $arguments      @TODO.
	 */
	public function __invoke( $args, $arguments ) {

		// Fetch integration.
		$this->integration = WP_CKWC_Integration();

		WP_CLI::log( 'ConvertKit for WooCommerce: Sync Past Orders: Started' );

		// Fetch all WooCommerce Orders not sent to ConvertKit.
		$order_ids = WP_CKWC()->get_class( 'order' )->get_orders_not_sent_to_convertkit();
		
		// Bail if no Orders exist, or all Orders sent to ConvertKit.
		if ( ! $order_ids ) {
			WP_CLI::log( 'No outstanding Orders to send to ConvertKit' );
			return;
		}

		// Iterate through Orders.
		foreach ( $order_ids as $id ) {
			// Send purchase data for this Order to ConvertKit.
			// We deliberately set the old status and new status to be different, and the new status to match
			// the integration's Purchase Data Event setting, otherwise the Order won't be sent to ConvertKit's Purchase Data.
			$result = WP_CKWC()->get_class( 'order' )->send_purchase_data(
				$id,
				'new', // old status.
				$this->integration->get_option( 'send_purchases_event' ) // new status.
			);

			// Output a warning and continue to the next Order, if the result is a WP_Error.
			if ( is_wp_error( $result ) ) {
				WP_CLI::warning(
					sprintf(
						/* translators: %1$s: WooCommerce Order ID, %2$s: Error message */
						__( 'WooCommerce Order ID #%1$s: %2$s', 'woocommerce-convertkit' ),
						$id,
						$result->get_error_message()
					)
				);
				continue;
			}

			// Output a success message.
			WP_CLI::success(
				sprintf(
					/* translators: %1$s: WooCommerce Order ID, %2$s: ConvertKit API Purchase ID */
					__( 'WooCommerce Order ID #%1$s added to ConvertKit Purchase Data successfully. ConvertKit Purchase ID: #%2$s', 'woocommerce-convertkit' ),
					$id,
					$result['id']
				)
			);
		}

		WP_CLI::log( 'ConvertKit for WooCommerce: Sync Past Orders: Finished' );

	}

}
