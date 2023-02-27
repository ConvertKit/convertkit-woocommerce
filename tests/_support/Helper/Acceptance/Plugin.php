<?php
namespace Helper\Acceptance;

/**
 * Helper methods and actions related to the ConvertKit Plugin,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   1.9.6
 */
class Plugin extends \Codeception\Module
{
	/**
	 * Helper method to activate the ConvertKit Plugin, checking
	 * it activated and no errors were output.
	 *
	 * @since   1.9.6
	 *
	 * @param   AcceptanceTester $I Acceptance Tester.
	 */
	public function activateConvertKitPlugin($I)
	{
		$I->activateThirdPartyPlugin($I, 'convertkit-for-woocommerce');
	}

	/**
	 * Helper method to deactivate the ConvertKit Plugin, checking
	 * it activated and no errors were output.
	 *
	 * @since   1.9.6
	 *
	 * @param   AcceptanceTester $I Acceptance Tester.
	 */
	public function deactivateConvertKitPlugin($I)
	{
		$I->deactivateThirdPartyPlugin($I, 'convertkit-for-woocommerce');
	}

	/**
	 * Helper method to activate the following Plugins:
	 * - WooCommerce
	 * - WooCommerce Stripe Gateway
	 * - ConvertKit for WooCommerce
	 *
	 * @since   1.0.0
	 *
	 * @param   AcceptanceTester $I Acceptance Tester.
	 */
	public function activateWooCommerceAndConvertKitPlugins($I)
	{
		// Activate ConvertKit Plugin.
		$I->activateConvertKitPlugin($I);

		// Activate WooCommerce Plugin.
		$I->activateThirdPartyPlugin($I, 'woocommerce');

		// Activate WooCommerce Stripe Gateway Plugin.
		$I->activateThirdPartyPlugin($I, 'woocommerce-gateway-stripe');

		// Flush Permalinks by visiting Settings > Permalinks, so that newly registered Post Types e.g.
		// WooCommerce Products work.
		$I->amOnAdminPage('options-permalink.php');
	}

	/**
	 * Helper method to deactivate the following Plugins:
	 * - WooCommerce
	 * - WooCommerce Stripe Gateway
	 * - ConvertKit for WooCommerce
	 *
	 * @since   1.0.0
	 *
	 * @param   AcceptanceTester $I Acceptance Tester.
	 */
	public function deactivateWooCommerceAndConvertKitPlugins($I)
	{
		$I->deactivateThirdPartyPlugin($I, 'convertkit-for-woocommerce');

		// Deactivate WooCommerce Stripe Gateway before WooCommerce, to prevent WooCommerce throwing a fatal error.
		$I->deactivateThirdPartyPlugin($I, 'woocommerce-gateway-stripe');
		$I->deactivateThirdPartyPlugin($I, 'woocommerce');
	}

	/**
	 * Helper method to setup the Plugin's API Key and Secret.
	 *
	 * @since   1.9.6
	 *
	 * @param   AcceptanceTester $I          			Acceptance Tester.
	 * @param   bool|string      $apiKey     			API Key (if specified, used instead of CONVERTKIT_API_KEY).
	 * @param   bool|string      $apiSecret  			API Secret (if specified, used instead of CONVERTKIT_API_SECRET).
	 * @param 	string 			 $subscriptionEvent 	Subscribe Event.
	 * @param 	bool|string 	 $subscription 			Form, Tag or Sequence to subscribe customer to.
	 * @param 	string 			 $nameFormat 			Name Format.
	 * @param 	bool 			 $mapCustomFields 		Map Order data to Custom Fields.
	 * @param 	bool 			 $displayOptIn 		 	Display Opt-In Checkbox.
	 * @param 	bool 			 $sendPurchaseDataEvent Send Purchase Data to ConvertKit on Order Event.
	 */
	public function setupConvertKitPlugin(
		$I,
		$apiKey = false,
		$apiSecret = false,
		$subscriptionEvent = 'pending',
		$subscription = false,
		$nameFormat = 'first',
		$mapCustomFields = false,
		$displayOptIn = false,
		$sendPurchaseDataEvent = false
	)
	{
		// Define Plugin's settings.
		$I->haveOptionInDatabase(
			'woocommerce_ckwc_settings',
			[
				'enabled'	   					=> 'yes',
				'api_key'      					=> ( $apiKey !== false ? $apiKey : $_ENV['CONVERTKIT_API_KEY'] ),
				'api_secret'   					=> ( $apiSecret !== false ? $apiSecret : $_ENV['CONVERTKIT_API_SECRET'] ),
				'event' 	   					=> $subscriptionEvent,
				'subscription' 	   				=> ( $subscription ? $subscription : '' ),
				'name_format' 	   				=> $nameFormat,

				// Custom Field mappings.
				'custom_field_phone' 	   		=> ( $mapCustomFields ? 'phone_number' : '' ),
				'custom_field_billing_address' 	=> ( $mapCustomFields ? 'billing_address' : '' ),
				'custom_field_shipping_address' => ( $mapCustomFields ? 'shipping_address' : '' ),
				'custom_field_payment_method' 	=> ( $mapCustomFields ? 'payment_method' : '' ),
				'custom_field_customer_note' 	=> ( $mapCustomFields ? 'notes' : '' ),

				// Opt-In Checkbox.
				'display_opt_in' 	   			=> ( $displayOptIn ? 'yes' : 'no' ),
				'opt_in_label' 	   				=> 'I want to subscribe to the newsletter',
				'opt_in_status' 	   			=> 'checked',
				'opt_in_location' 	   			=> 'billing',

				// Purchase Data.
				'send_purchases'				=> ( $sendPurchaseDataEvent ? 'yes' : 'no' ),
				'send_purchases_event'			=> ( $sendPurchaseDataEvent ? $sendPurchaseDataEvent : '' ),

				// Debug.
				'debug' 						=> 'yes',
			]
		);
	}

	/**
	 * Helper method to reset the ConvertKit Plugin settings, as if it's a clean installation.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   AcceptanceTester $I Acceptance Tester.
	 */
	public function resetConvertKitPlugin($I)
	{
		// Plugin Settings.
		$I->dontHaveOptionInDatabase('woocommerce_ckwc_settings');

		// Resources.
		$I->dontHaveOptionInDatabase('ckwc_custom_fields');
		$I->dontHaveOptionInDatabase('ckwc_forms');
		$I->dontHaveOptionInDatabase('ckwc_sequences');
		$I->dontHaveOptionInDatabase('ckwc_tags');

		// Review Request.
		$I->dontHaveOptionInDatabase('convertkit-for-woocommerce-review-request');
		$I->dontHaveOptionInDatabase('convertkit-for-woocommerce-review-dismissed');
	}

	/**
	 * Helper method to delete option table rows for review requests.
	 * Useful for resetting the review state between tests.
	 *
	 * @since   1.4.3
	 *
	 * @param   AcceptanceTester $I Acceptance Tester.
	 */
	public function deleteConvertKitReviewRequestOptions($I)
	{
		$I->dontHaveOptionInDatabase('convertkit-for-woocommerce-review-request');
		$I->dontHaveOptionInDatabase('convertkit-for-woocommerce-review-dismissed');
	}

	/**
	 * Helper method to load the WooCommerce > Settings > Integration > ConvertKit screen.
	 *
	 * @since   1.0.0
	 *
	 * @param   AcceptanceTester $I Acceptance Tester.
	 */
	public function loadConvertKitSettingsScreen($I)
	{
		$I->amOnAdminPage('admin.php?page=wc-settings&tab=integration&section=ckwc');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Helper method to determine the order of <option> values for the given select element
	 * and values when <optgroup> is used within a <select>.
	 *
	 * @since   1.5.7
	 *
	 * @param   AcceptanceTester $I             AcceptanceTester.
	 * @param   string           $selectElement <select> element.
	 */
	public function checkSelectWithOptionGroupsOptionOrder($I, $selectElement)
	{
		// Define options.
		$options = [
			'ckwc-sequences' => [ // <optgroup> ID.
				'Another Sequence', // First item.
				'WordPress Sequence', // Last item.
			],
			'ckwc-forms'     => [ // <optgroup> ID.
				'AAA Test', // First item.
				'WooCommerce Product Form', // Last item.
			],
			'ckwc-tags'      => [ // <optgroup> ID.
				'gravityforms-tag-1', // First item.
				'wordpress', // Last item.
			],
		];

		// Confirm ordering.
		foreach ( $options as $optgroup => $values ) {
			foreach ( $values as $i => $value ) {
				// Define the applicable CSS selector.
				if ( $i === 0 ) {
					$nth = 'first-child';
				} elseif ( $i + 1 === count( $values ) ) {
					$nth = 'last-child';
				} else {
					$nth = 'nth-child(' . ( $i + 1 ) . ')';
				}

				$I->assertEquals(
					$I->grabTextFrom('select' . $selectElement . ' optgroup#' . $optgroup . ' option:' . $nth),
					$value
				);
			}
		}
	}

	/**
	 * Helper method to determine that the order of the Form resources in the given
	 * select element are in the expected alphabetical order.
	 *
	 * @since   1.5.7
	 *
	 * @param   AcceptanceTester $I                 AcceptanceTester.
	 * @param   string           $selectElement     <select> element.
	 * @param   bool|array       $prependOptions    Option elements that should appear before the resources.
	 */
	public function checkSelectCustomFieldOptionOrder($I, $selectElement, $prependOptions = false)
	{
		// Define options.
		$options = [
			'Billing Address', // First item.
			'Test', // Last item.
		];

		// Prepend options, such as 'Default' and 'None' to the options, if required.
		if ( $prependOptions ) {
			$options = array_merge( $prependOptions, $options );
		}

		// Check order.
		$I->checkSelectOptionOrder($I, $selectElement, $options);
	}

	/**
	 * Helper method to determine the order of <option> values for the given select element
	 * and values.
	 *
	 * @since   1.5.7
	 *
	 * @param   AcceptanceTester $I             AcceptanceTester.
	 * @param   string           $selectElement <select> element.
	 * @param   array            $values        <option> values.
	 */
	public function checkSelectOptionOrder($I, $selectElement, $values)
	{
		foreach ( $values as $i => $value ) {
			// Define the applicable CSS selector.
			if ( $i === 0 ) {
				$nth = 'first-child';
			} elseif ( $i + 1 === count( $values ) ) {
				$nth = 'last-child';
			} else {
				$nth = 'nth-child(' . ( $i + 1 ) . ')';
			}

			$I->assertEquals(
				$I->grabTextFrom('select' . $selectElement . ' option:' . $nth),
				$value
			);
		}
	}
}
