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
	 * @param   array $args           Non-named arguments.
	 * @param   array $arguments      Named arguments.
	 */
	public function __invoke( $args, $arguments ) {

		// Fetch integration.
		$this->integration = WP_CKWC_Integration();

		WP_CLI::log( __( 'Kit for WooCommerce: Sync Past Orders: Started', 'woocommerce-convertkit' ) );

		// Bail if the integration isn't enabled.
		if ( ! $this->integration->is_enabled() ) {
			WP_CLI::error( __( 'Please connect your Kit account and enable the integration at WooCommerce > Settings > Integration > Kit.', 'woocommerce-convertkit' ) );
		}

		// Fetch all WooCommerce Orders not sent to ConvertKit.
		$order_ids = WP_CKWC()->get_class( 'order' )->get_orders_not_sent_to_convertkit();

		// Bail if no Orders exist, or all Orders sent to ConvertKit.
		if ( ! $order_ids ) {
			WP_CLI::log( __( 'No outstanding Orders to send to Kit', 'woocommerce-convertkit' ) );
			return;
		}

		// Log number of orders found.
		WP_CLI::log(
			sprintf(
				/* translators: Number of WooCommerce Orders found not synchronised to ConvertKit */
				__( 'Kit for WooCommerce: Sync Past Orders: %s orders found not synchronised to Kit.', 'woocommerce-convertkit' ),
				count( $order_ids )
			)
		);

		// Iterate through Orders.
		foreach ( $order_ids as $index => $id ) {
			// If a limit argument was specified and has been hit, don't process further orders.
			if ( array_key_exists( 'limit', $arguments ) && $index >= $arguments['limit'] ) {
				break;
			}

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
					__( 'WooCommerce Order ID #%1$s added to Kit Purchase Data successfully. Kit Purchase ID: #%2$s', 'woocommerce-convertkit' ),
					$id,
					$result['purchase']['id']
				)
			);
		}

		WP_CLI::log( 'Kit for WooCommerce: Sync Past Orders: Finished' );

	}

}
