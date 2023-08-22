/**
 * Synchronous AJAX Requests.
 *
 * This jQuery plugin allows feature rich web applications to send a specified number of requests, sequentially and synchronously,
 * to a given endpoint, binding the progress feedback to a jQuery UI Progressbar.
 *
 * This plugin acts as a wrapper for $.post, with some extra callback functions when each request succeeds or fails, plus a final
 * callback function when the entire routine completes.
 *
 * The advantage of this approach is that the UI is not locked - so updates can be posted to the web page - and the server isn't flooded
 * with 100 requests at once.  Each request must complete before the next one can run.
 *
 * Your server-side script will be sent all data as a POST array, including POST['current_index'], telling your script what number this
 * request is.
 *
 * @package CKWC
 * @author ConvertKit
 */

( function ( $ ) {

	/**
	 * Init Synchronous Request
	 *
	 * @param object options Override Default Settings
	 */
	$.fn.synchronous_request = function ( options ) {

		// Default Settings.
		var settings = $.extend(
			{
				// Required.
				url: 			'', // AJAX url.
				number_requests:0, // Total number of requests that will be sent.
				offset: 		0, // The offset to start at.
				action:         '', // The WordPress registered AJAX action name to use for each request.
				nonce: 			'', // WordPress nonce, which your AJAX function should validate.
				ids:            '', // Array of IDs or keys to iterate through, sending one with each request.
				wait: 			5000, // Number of milliseconds to wait.
				stop_on_error: 	0, // 1: stop, 0: continue and retry the same request, -1: continue but skip the failed request.

				// Optional.
				progress_count: '#progress-number', // DOM selector that contains successful request count.
				log:  			'#log', // DOM selector for the log.
				cancel_button:  '.cancel', // DOM selector for the cancel button.
				type: 			'post', // AJAX request type.
				cache: 			false, // Whether to cache requests.
				dataType: 		'json', // Response data type.

				/**
				 * Called when an AJAX request returns a successful response.
				 *
				 * @since   1.4.3
				 *
				 * @param   object  response        Response
				 * @param   int     currentIndex    Current Index
				 */
				onRequestSuccess: function ( response, currentIndex ) {

					// Maybe reset log if it's more than 100 lines, for UI performance.
					this.maybeResetLog();

					if ( response.success ) {
						// Output Log.
						$( 'ul', $( this.log ) ).append( '<li class="success">' + ( currentIndex + 1 ) + '/' + this.number_requests + ': ' + response.data + '</li>' );
					} else {
						// Something went wrong.
						// Define message.
						var message = ( currentIndex + 1 ) + '/' + this.number_requests + ': Response Error: ' + response.data;
						switch ( this.stop_on_error ) {
							// Stop sending any further requests.
							case 1:
								break;

							// Continue, reattempting the failed request.
							case 0:
								message = message + '. Waiting ' + ( this.stop_on_error_pause / 1000 ) + ' seconds before reattempting this request.';
								break;

							// Continue, skipping the failed request.
							case -1:
								message = message + '. Waiting ' + ( this.stop_on_error_pause / 1000 ) + ' seconds before attempting next request.';
								break;
						}

						// Output Log.
						$( 'ul', $( this.log ) ).append( '<li class="error">' + message + '</li>' );
					}

					// Run the next request, unless the user clicked the 'Stop Generation' button.
					if ( this.cancelled == true ) {
						return false;
					}

					// Run the next request.
					return true;

				},

				/**
				 * Called when an AJAX request results in a HTTP or server error.
				 *
				 * @since   1.4.3
				 */
				onRequestError: function ( xhr, textStatus, e, currentIndex ) {

					// If the log exceeds 100 items, reset it.
					if ( $( '#log ul li' ).length >= 100 ) {
						$( '#log ul' ).html( '' );
					}

					// Output Log.
					$( '#log ul' ).append( '<li class="error">' + ( currentIndex + 1 ) + '/' + ckwc_sync_past_orders.number_requests + ': Request Error: ' + xhr.status + ' ' + xhr.statusText + '</li>' );

					// Run the next request, unless the user clicked the cancel button.
					if ( this.cancelled == true ) {
						return false;
					}

					// Try again.
					return true;

				},

				/**
				 * Called when all requests have completed, or the user cancelled.
				 *
				 * @since   1.4.3
				 */
				onFinished: function () {

					if ( this.cancelled ) {
						$( 'ul', $( this.log ) ).append( '<li class="success">Process cancelled by user.</li>' );
					} else {
						$( 'ul', $( this.log ) ).append( '<li class="success">Finished.</li>' );

						// Disable the cancel button.
						$( settings.cancel_button ).attr( 'disabled', 'disabled' );
					}

				},

				/**
				 * If the on screen log exceeds 100 entries, clear it
				 * for UI / browser performance.
				 *
				 * @since 	1.4.3
				 */
				maybeResetLog: function () {

					// If the log exceeds 100 items, reset it.
					if ( $( 'ul li', $( this.log ) ).length >= 100 ) {
						$( 'ul', $( this.log ) ).html( '' );
					}

				}
			},
			options
		);

		// Initialize Progress Bar.
		progressbar = $( this ).progressbar(
			{
				value: 0
			}
		);

		// Bind a listener to the cancel button.
		if ( settings.cancel_button ) {

			$( settings.cancel_button ).on(
				'click',
				function ( e ) {

					e.preventDefault();
					settings.cancelled = true;

					// Disable the cancel button.
					$( settings.cancel_button ).attr( 'disabled', 'disabled' );

				}
			);

		}

		// Initialize first request.
		synchronousAjaxRequest( settings, ( -1 + Number( settings.offset ) ), progressbar, settings.progress_count );

	};

	/**
	 * Main function to perform an AJAX request.
	 *
	 * @since 	1.4.3
	 */
	function synchronousAjaxRequest( settings, currentIndex, progressbar, progressCounter ) {

		// Increment .
		currentIndex++;

		// If currentIndex exceeds or equals settings.number_requests, we have finished
		// currentIndex is a zero based count.
		if ( currentIndex > ( Number( settings.offset ) + Number( settings.number_requests ) - 1 ) ) {
			// Call completion closure.
			settings.onFinished();
			return true;
		}

		// Send AJAX request.
		$.ajax(
			{
				url:      	settings.url,
				type:     	settings.type,
				async:    	true,
				cache:    	settings.cache,
				dataType: 	settings.dataType,
				data: 		{
					action: 		settings.action,
					nonce: 			settings.nonce,
					id: 			settings.ids[ currentIndex ],
					current_index: 	currentIndex
				},
				success: function ( response ) {

					// Call onRequestSuccess closure.
					var cancelled = settings.onRequestSuccess( response, currentIndex );

					// If the response indicates success, update the progress bar and count.
					if ( response.success ) {
						progressbar.progressbar( 'value', Number( ((currentIndex + 1) / settings.number_requests) * 100 ) );
						$( progressCounter ).text( ( currentIndex + 1 ) );
					} else {
						// If Stop on Error is enabled, call onFinished closure and exit.
						if ( settings.stop_on_error == 1 ) {
							settings.onFinished();
							return;
						}

						// If stop on Error is zero, decrement the currentIndex so the same request is attempted again.
						if ( settings.stop_on_error == 0 ) {
							currentIndex--;
						}
					}

					// If false was returned from the closure, the calling script has requested we stop the loop
					// Call onFinished closure and exit.
					if ( ! cancelled ) {
						settings.onFinished();
						return;
					}

					// If the response indicates an error, wait the required period of time before sending the
					// next request.
					if ( ! response.success ) {
						setTimeout(
							function () {
								// Start next request.
								synchronousAjaxRequest( settings, currentIndex, progressbar, progressCounter );
								return;
							},
							settings.wait
						);
					} else {
						// Start next request.
						synchronousAjaxRequest( settings, currentIndex, progressbar, progressCounter );
						return;
					}

				},
				error: function (xhr, textStatus, e) {

					// Call closure.
					var cancelled = settings.onRequestError( xhr, textStatus, e, currentIndex );

					// If Stop on Error is enabled, call onFinished closure and exit.
					if ( settings.stop_on_error == 1 ) {
						settings.onFinished();
						return;
					}

					// If stop on Error is zero, decrement the currentIndex so the same request is attempted again.
					if ( settings.stop_on_error == 0 ) {
						currentIndex--;
					}

					// If false was returned from the closure, the calling script has requested we stop the loop.
					// Call onFinished closure and exit.
					if ( ! cancelled ) {
						settings.onFinished();
						return;
					}

					// Wait the required period of time before sending the next request.
					setTimeout(
						function () {
							// Start next request.
							synchronousAjaxRequest( settings, currentIndex, progressbar, progressCounter );
							return;
						},
						settings.wait
					);

				}
			}
		);
	}

} )( jQuery );
