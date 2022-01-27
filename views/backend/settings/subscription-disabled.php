<?php
/**
 * Outputs the Subscription setting table row for the integration's settings,
 * when the integration is disabled or does not have valid API credentials.
 *
 * @package CKWC
 * @author ConvertKit
 */

?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
		<?php echo esc_html( $this->get_tooltip_html( $data ) ); ?>
	</th>
	<td class="forminp">
		<?php esc_html_e( 'To select the Form, Tag or Sequence to subscribe Customers to, specify a valid API Key and Secret, and click Save changes.', 'woocommerce-convertkit' ); ?>
	</td>
</tr>
