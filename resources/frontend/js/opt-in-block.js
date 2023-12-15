/**
 * Opt-in Block for Gutenberg
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Registers the opt-in block within the WooCommerce Checkout, and defines
 * its output on the frontend site.
 *
 * @since   1.7.1
 *
 * @package CKWC
 * @author ConvertKit
 */
( function ( element, checkout, settings ) {

	// Define some constants for the various items we'll use.
	const el = element.createElement;
	const {
		useEffect,
		useState
	}        = element;
	const {
		registerCheckoutBlock,
		CheckboxControl
	}        = checkout;

	// Get settings from WooCommerce; this calls the get_script_data()
	// PHP method.
	const {
		getSetting
	} = settings;
	const {
		enabled,
		displayOptIn,
		optInLabel,
		optInStatus,
		metadata
	} = getSetting( 'ckwc_opt_in_data' );

	// Register the block with the WooCommerce Checkout Block.
	registerCheckoutBlock(
		{
			metadata: metadata,
			component: function ( props ) {

				// If the integration is disabled or set not to display the opt in checkbox at checkout,
				// don't render anything.
				if ( ! enabled ) {
					return null;
				}
				if ( ! displayOptIn ) {
					return null;
				}

				// Store the checkbox checked state in WooCommerce's extension data.
				// See register_opt_in_checkbox_store_api_endpoint() PHP method that registers expected data.
				const [ checked, setChecked ]   = useState( ( optInStatus === 'checked' ? true : false ) );
				const { checkoutExtensionData } = props;
				const { setExtensionData }      = checkoutExtensionData;

				useEffect(
					function () {
						setExtensionData( 'ckwc-opt-in', 'ckwc_opt_in', checked );
					},
					[
						checked,
						setExtensionData
					]
				);

				// Return the opt-in checkbox component to render on the frontend checkout.
				return (
					el(
						'div',
						{},
						el(
							CheckboxControl,
							{
								id: 'ckwc_opt_in',
								label: optInLabel,
								checked: checked,
								onChange: setChecked
							}
						)
					)
				);

			}
		}
	);

} (
	window.wp.element,
	window.wc.blocksCheckout,
	window.wc.wcSettings
) );
