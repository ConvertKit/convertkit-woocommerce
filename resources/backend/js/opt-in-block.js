/**
 * Opt-in Block for Gutenberg
 *
 * @package CKWC
 * @author ConvertKit
 */

console.log( 'backend' );

/**
 * Registers the opt-in block in the Gutenberg editor.
 *
 * @since   1.7.1
 *
 * @package CKWC
 * @author ConvertKit
 */
( function ( blocks, editor, element, components ) {

	// Define some constants for the various items we'll use.
	const el                    = element.createElement;
	const {
		registerBlockType
	} 							= blocks;
	const { InspectorControls } = editor;
	const {
		CheckboxControl,
		Panel,
		PanelBody,
		PanelRow
	}                           = components;

	// Register Block.
	registerBlockType(
		'ckwc/opt-in',
		{
			title: 'ConvertKit Opt In',
			category: 'woocommerce',
			description: 'Displays a ConvertKit opt in checkbox at Checkout.',
			//icon:       getIcon,
			keywords: [
				'subscriber',
				'newsletter',
				'email',
				'convertkit',
				'opt in',
				'checkout'
			],
			supports: {
				'html': false,
				'align': false,
				'multiple': false,
				'reusable': false
			},
			parent: [
				'woocommerce/checkout-fields-block'
			],
			attributes: {
				'lock': {
					'type': 'object',
					'default': {
						'remove': true,
						'move': true
					}
				},
				'ckwc_opt_in': {
					'type': 'boolean'
				}
			},

			// Editor.
			edit: function ( props ) {

				// If the integration is disabled or set not to display the opt in checkbox at checkout,
				// don't render anything.
				if ( ! ckwc_integration.enabled ) {
					return null;
				}
				if ( ! ckwc_integration.display_opt_in ) {
					return null;
				}

				// Return a render of the checkbox in the block editor as it would look
				// on the frontend checkout, based on the integration settings for the
				// checkbox checked state and its label.
				return (
					el(
						'div',
						{},
						[
							el(
								InspectorControls,
								{},
								el(
									PanelBody,
									{
										title: 'title',
										key: 'ckwc-opt-in-panel'
									}
								)
							),
							el(
								CheckboxControl,
								{
									id: 'ckwc-opt-in',
									checked: ( ckwc_integration.opt_in_status === 'checked' ? true : false ),
									label: ckwc_integration.opt_in_label,
								}
							)
						]
					)
				);

			},

			// Output.
			save: function ( props ) {

				// This isn't used on the frontend, as WooCommerce blocks operate a bit differently from typical blocks.
				// See resources/frontend/opt-in-block.js to define the checkout output.
				return null;

			},
		}
	);

} (
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.element,
	window.wp.components
) );
