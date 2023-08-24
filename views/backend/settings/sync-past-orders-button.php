<?php
/**
 * Outputs the Sync Past Orders table row for the integration's settings.
 *
 * @package CKWC
 * @author ConvertKit
 */

?>
<tr valign="top">
	<th scope="row" class="titledesc">&nbsp;</th>
	<td class="forminp">
		<fieldset>
			<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
			<a href="<?php echo esc_attr( $data['url'] ); ?>" id="ckwc_sync_past_orders" class="button button-secondary <?php echo esc_attr( $data['class'] ); ?>">
				<?php
				printf(
					/* translators: number of orders not sent to ConvertKit */
					esc_html( _n( 'Sync %s past order', 'Sync %s past orders', count( $unsynced_order_ids ), 'woocommerce-convertkit' ) ),
					esc_html( number_format_i18n( count( $unsynced_order_ids ) ) )
				);
				?>
			</a>
			<?php echo $this->get_description_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</fieldset>
	</td>
</tr>
