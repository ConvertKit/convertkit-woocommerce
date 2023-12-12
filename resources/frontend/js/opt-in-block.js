/**
 * Opt-in Block for Gutenberg
 *
 * @package CKWC
 * @author ConvertKit
 */

console.log( 'frontend' );

/**
 * Registers the opt-in block within the WooCommerce Checkout, and defines
 * its output on the frontend site.  
 *
 * @since   1.7.1
 *
 * @package CKWC
 * @author ConvertKit
 */
( function ( element, checkout ) {

	// Define some constants for the various items we'll use.
	const el                    = element.createElement;
	const { registerCheckoutBlock, CheckboxControl } = checkout;

	registerCheckoutBlock( {
		metadata: {
			name: 'ckwc/opt-in',
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
			    'woocommerce/checkout-fields-block'  // @TODO make dynamic per https://github.com/woocommerce/woocommerce-blocks/blob/trunk/packages/checkout/blocks-registry/README.md#inner-block-areas
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
			}
		},

		/**
		 * Renders an opt-in checkbox on the frontend WooCommerce Checkout.
		 * 
		 * @since 	1.7.1
		 */
		component: function( children, checkoutExtensionData ) {

			// If the integration is disabled or set not to display the opt in checkbox at checkout,
			// don't render anything.
			if ( ! ckwc_integration.enabled ) {
				return null;
			}
			if ( ! ckwc_integration.display_opt_in ) {
				return null;
			}

			return (
				el(
					'div',
					{},
					el(
						CheckboxControl,
						{
							id: 'ckwc-opt-in',
							checked: ( ckwc_integration.opt_in_status === 'checked' ? true : false ),
							label: ckwc_integration.opt_in_label,
						}
					)
				)
			);
		}
	} );

} (
	window.wp.element,
	window.wc.blocksCheckout
) );

