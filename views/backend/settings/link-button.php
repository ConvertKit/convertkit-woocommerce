<?php
/**
 * Outputs the Sync Past Orders table row for the integration's settings.
 *
 * @package CKWC
 * @author ConvertKit
 */

?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
		<?php echo esc_html( $this->get_tooltip_html( $data ) ); ?>
	</th>
	<td class="forminp">
		<fieldset>
			<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
			<a href="<?php echo esc_attr( $data['url'] ); ?>" id="<?php echo esc_attr( $key ); ?>" class="button button-secondary <?php echo esc_attr( $data['class'] ); ?>">
				<?php
				echo esc_html( $data['label'] );
				?>
			</a>
			<?php echo $this->get_description_html( $data ); // phpcs:ignore ?>
		</fieldset>
	</td>
</tr>
