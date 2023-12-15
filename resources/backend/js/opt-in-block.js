/**
 * Opt-in Block for Gutenberg
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Registers the opt-in block in the Gutenberg editor.
 *
 * @since   1.7.1
 *
 * @package CKWC
 * @author ConvertKit
 */
( function ( blocks, editor, element, components, settings ) {

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

	// Get settings from WooCommerce; this calls the get_script_data()
	// PHP method.
	const {
		getSetting
	} = settings;
	const {
		enabled,
		displayOptIn,
		optInLabel,
		optInStatus
	} = getSetting( 'ckwc_opt_in_data' );

	// Register Block.
	registerBlockType(
		'ckwc/opt-in',
		{
			// Editor.
			edit: function ( props ) {

				// If the integration is disabled or set not to display the opt in checkbox at checkout,
				// don't render anything.
				if ( ! enabled ) {
					return null;
				}
				if ( ! displayOptIn ) {
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
							),
							el(
								CheckboxControl,
								{
									id: 'ckwc_opt_in',
									checked: ( optInStatus === 'checked' ? true : false ),
									label: optInLabel,
									disabled: true // Required so it cannot be interacted with in the editor.
								}
							)
						]
					)
				);

			},

			// Output.
			save: function ( props ) {

				// This isn't used on the frontend, as WooCommerce Checkout blocks operate a bit differently from typical blocks.
				// See resources/frontend/opt-in-block.js to define the checkout output.
				return null;

			},
		}
	);

} (
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.element,
	window.wp.components,
	window.wc.wcSettings
) );
