jQuery(document).ready(function($) {
	$('#woocommerce_ckwc_display_opt_in').change(function() {
		var $dependents = $('[id^="woocommerce_ckwc_opt_in_"]').parents('tr');

		$dependents.toggle($(this).prop('checked'));
	}).trigger('change');
});
