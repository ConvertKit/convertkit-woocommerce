/**
 * Synchronous AJAX function to send Orders to ConvertKit.
 *
 * @since 	1.4.3
 */
function ckwcSyncPastOrders() {

    ( function( $ ) {

        $( '#progress-bar' ).synchronous_request( {
            url:                ajaxurl,
            number_requests:    ckwc_sync_past_orders.number_of_requests,
            offset:             ckwc_sync_past_orders.resume_index,
            data: {
                id:             ckwc_sync_past_orders.id,
                action:         ckwc_sync_past_orders.action,
            },
            wait:               ckwc_sync_past_orders.stop_on_error_pause,
            stop_on_error:      ckwc_sync_past_orders.stop_on_error,
        } );

    } )( jQuery );

}

ckwcSyncPastOrders();