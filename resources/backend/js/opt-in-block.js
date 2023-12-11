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
( function ( blocks, editor, element, components ) {

	// Define some constants for the various items we'll use.
	const el                    = element.createElement;
	const { registerBlockType } = blocks;
	const { InspectorControls } = editor;
	const {
		Fragment,
		useState
	}                           = element;
	const {
		Button,
		Dashicon,
		TextControl,
		SelectControl,
		ToggleControl,
		Panel,
		PanelBody,
		PanelRow
	}                           = components;

	// Register Block.
	registerBlockType(
		'ckwc/opt-in',
		{
			title:      'ckwc opt in',
			description:'Adds a ConvertKit opt in checkbox to the checkout.',
			category:   'woocommerce',
			icon:       getIcon,
			keywords: 	block.keywords,
			attributes: block.attributes,
			supports: 	block.supports,
			example: 	{
				attributes: {
					is_gutenberg_example: true,
				}
			},

			// Editor.
			edit: function ( props ) {

				// Deliberate; opt in label and default status is defined in Plugin's settings.
				return null;

			},

			// Output.
			save: function ( props ) {

				// Deliberate; preview in the editor is determined by the return statement in `edit` above.
				// On the frontend site, the block's render() PHP class is always called, so we dynamically
				// fetch the content.
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

