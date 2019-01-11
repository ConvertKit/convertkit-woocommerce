"use strict";

(function($) {

    /**
	 * Find the Unsynced Orders.
	 *
     * @param step
     * @param total
     */
    function ckFindUnsyncedOrders( step, total ) {
        var _step  = step || 1,
            _total = total || 0,
			spinner = $( '#ckwcOrderData' ).parent().find('.spinner');

        if ( ! spinner.hasClass('is-active') ) {
        	spinner.addClass('is-active');
		}

        $.ajax({
			url: ajaxurl,
			data: { action: 'ckwc_find_unsynced_orders', step: _step, total: _total },
			method: 'GET',
			success: function( resp ) {
				if ( resp.success ) {
					if ( typeof resp.data.unsynced !== 'undefined' ) {
						$('#unsyncedOrders').find('strong').html( resp.data.unsynced );
						$('.ckwc-sync-unsynced').removeClass('hidden');
					}
					if ( ! resp.data.done ) {
						var next_step = resp.data.step || _step++;
                        ckFindUnsyncedOrders( next_step, _total );
					} else {
                        spinner.removeClass('is-active');
					}
				}
			}
        });
    }

    /**
     * Find the Unsynced Orders.
     *
     * @param step
     * @param total
     */
    function ckSyncOrders( step, total ) {
        var _step    = step || 1,
            spinner  = $( '#ckwcSyncOrders' ).parent().find('.spinner'),
            progress = $( '#ckwcSyncOrders' ).parent().find('.ckwc-progress');

        if ( ! spinner.hasClass('is-active') ) {
            spinner.addClass('is-active');
        }

        progress.removeClass('hidden');
        progress.html( '0%' );

        $.ajax({
            url: ajaxurl,
            data: { action: 'ckwc_sync_orders', step: _step, total: total },
            method: 'GET',
            success: function( resp ) {
                if ( resp.success ) {
                    if ( ! resp.data.done ) {
                        var next_step = resp.data.step || _step++;
                        var _total    = resp.data.total || total;
                        progress.html( resp.data.progress + '%' );
                        ckSyncOrders( next_step, _total );
                    } else {
                        spinner.removeClass('is-active');
                        progress.addClass('hidden');
                    }
                }
            }
        });
    }

	$(function(){
        $('#woocommerce_ckwc_display_opt_in').change(function() {
            var $dependents = $('[id^="woocommerce_ckwc_opt_in_"]').parents('tr');

            $dependents.toggle($(this).prop('checked'));
        }).trigger('change');

        $('#ckwcOrderData').on( 'click', function(){
            var step = 1,
                total = parseInt( $(this).attr('data-order-count') );

            ckFindUnsyncedOrders( 1, total );
        });

        $('#ckwcSyncOrders').on( 'click', function(){
            var step = 1;
            ckSyncOrders( step, 0 );
		});
	});

})(jQuery);