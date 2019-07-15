jQuery(document).ready(function($) {
	$('#woocommerce_ckwc_display_opt_in').change(function() {
		var $dependents = $('[id^="woocommerce_ckwc_opt_in_"]').parents('tr');

		$dependents.toggle($(this).prop('checked'));
	}).trigger('change');


	$(document).on('click', '#refresh_ckwc_subscription_options', refreshSubscriptionOptions);

});

refreshSubscriptionOptions = function (e) {
	e.preventDefault();
	startSpinner();
	$.ajax({
		url: window.ajaxurl,
		data: { action: 'ckwc_refresh_subscription_options', api_key: $('#woocommerce_ckwc_api_key').val() },
		success: function (resp) {
		    console.log(resp);
			if ( resp.success ) {
				$('#woocommerce_ckwc_subscription').replaceWith( resp.data );
			} else {
				alert( resp.data );
			}
		},
		error: function( resp ) {
			alert( resp.statusText );
		},
		complete: function () {
			stopSpinner();
		}
	});
};

startSpinner = function () {
	$('#refreshCKSpinner').addClass('is-active').css('float', 'none');
};

stopSpinner = function () {
	$('#refreshCKSpinner').removeClass('is-active');
};