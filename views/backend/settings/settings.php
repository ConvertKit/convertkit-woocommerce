<?php
/**
 * Outputs the Settings screen, with markup that enables styling
 * to be applied to improve the UI.
 *
 * @package CKWC
 * @author ConvertKit
 */

?>

<div class="postbox">
	<h2><?php echo esc_html( $this->get_method_title() ); ?></h2>
	<?php echo wp_kses_post( wpautop( $this->get_method_description() ) ); ?>

	<div><input type="hidden" name="section" value="<?php echo esc_attr( $this->id ); ?>" /></div>

	<table class="form-table ckwc">
		<?php echo $this->generate_settings_html( $this->get_form_fields(), false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</table>
</div>
