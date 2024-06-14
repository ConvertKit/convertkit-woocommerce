<?php
/**
 * Outputs the Account Name table row for the integration's settings, including
 * a button to disconnect OAuth.
 *
 * @package CKWC
 * @author ConvertKit
 */

?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
	</th>
	<td class="forminp">
		<fieldset>
			<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
			<p class="description">
				<?php
				echo esc_html( $this->account_name );
				?>
			</p>
			<a href="<?php echo esc_attr( $data['url'] ); ?>" id="<?php echo esc_attr( $key ); ?>" class="button button-secondary">
				<?php
				echo esc_html( $data['label'] );
				?>
			</a>
		</fieldset>
	</td>
</tr>
