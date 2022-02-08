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
			<a href="<?php echo $data['url']; ?>" id="ckwc_sync_past_orders" class="button button-secondary">
				<?php
				echo sprintf(
					_n( 'Sync %s Past Order', 'Sync %s Past Orders', count( $unsynced_order_ids ), 'woocommerce-convertkit' ),
					number_format_i18n( count( $unsynced_order_ids ) )
				);
				?>
			</a>
			<?php echo $this->get_description_html( $data ); // phpcs:ignore ?>
		</fieldset>
	</td>
</tr>
