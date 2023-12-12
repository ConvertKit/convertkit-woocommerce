<?php
/**
 * Outputs the opt in checkbox block for the WooCoommerce Checkout Block
 *
 * @package CKWC
 * @author ConvertKit
 */

?>

<div class="wc-block-components-checkbox">
	<label for="ckwc_opt_in">
		<input type="checkbox" name="ckwc_opt_in" id="ckwc_opt_in" class="wc-block-components-checkbox__input"<?php echo ( $this->integration->get_option( 'opt_in_status' ) === 'checked' ? ' checked' : '' ); ?>/>  
		<svg class="wc-block-components-checkbox__mark" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 20">
			<path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"></path>
		</svg>
		<span class="wc-block-components-checkbox__label">
			<?php echo esc_html( $this->integration->get_option( 'opt_in_label' ) ); ?>
		</span>
	</label>
</div>