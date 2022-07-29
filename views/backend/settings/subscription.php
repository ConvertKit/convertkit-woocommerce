<?php
/**
 * Outputs the Subscription setting table row for the integration's settings.
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
		<fieldset>
			<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
			<?php
			// Load subscription dropdown field.
			require CKWC_PLUGIN_PATH . '/views/backend/subscription-dropdown-field.php';
			echo $this->get_description_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>
		</fieldset>
	</td>
</tr>
