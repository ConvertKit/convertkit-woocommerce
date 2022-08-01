/**
 * Refresh Resources
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Refreshes sequences, forms and tags when the Refresh button is clicked.
 *
 * @since 	1.4.8
 */
jQuery( document ).ready(
	function( $ ) {

		$( 'button.ckwc-refresh-resources' ).on(
			'click',
			function( e ) {

				e.preventDefault();

				// Fetch some DOM elements.
				var button = this,
				resource   = $( button ).data( 'resource' ),
				field      = $( button ).data( 'field' );

				// Disable button.
				$( button ).prop( 'disabled', true );

				// Perform AJAX request to refresh resource.
				$.ajax(
					{
						type: 'POST',
						data: {
							action: 'ckwc_admin_refresh_resources',
							nonce: ckwc_admin_refresh_resources.nonce
						},
						url: ckwc_admin_refresh_resources.ajaxurl,
						success: function ( response ) {

							if ( ckwc_admin_refresh_resources.debug ) {
								console.log( response );
							}

							// Get currently selected option.
							var selectedOption = $( field ).val();
							
							// Remove existing select options.
							$( 'option', $( field ) ).each(
								function() {
									// Skip if data-preserve-on-refresh is specified, as this means we want to keep this specific option.
									// This will be present on the 'None' and 'Default' options.
									if ( typeof $( this ).data( 'preserve-on-refresh' ) !== 'undefined' ) {
										return;
									}

									// Remove this option.
									$( this ).remove();
								}
							);

							// Populate each resource type with select options from response data into
							// the application option group.
							for ( const[ resource, resources ] of Object.entries( response.data ) ) {
								// resource = forms, sequences, tags.
								// resoruces = array of resources.
								resources.forEach(
									function( item ) {
										var value = $( 'optgroup#ckwc-' + resource, $( field ) ).data( 'option-value-prefix' ) + item.id;
										$( 'optgroup#ckwc-' + resource, $( field ) ).append(
											new Option(
												item.name,
												value,
												false,
												( selectedOption == value ? true : false )
											)
										);
									}
								);
							}

							// Trigger a change event on the select field, to allow Select2 instances to repopulate their options.
							$( field ).trigger( 'change' );

							// Enable button.
							$( button ).prop( 'disabled', false );

						}
					}
				).fail(
					function ( response ) {
						if ( ckwc_admin_refresh_resources.debug ) {
							console.log( response );
						}
					}
				);

			}
		);

	}
);
