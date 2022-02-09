<?php
/**
 * Tests for Sync Past Orders functionality.
 * 
 * @since 	1.4.3
 */
class SyncPastOrdersCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 * 
	 * @since 	1.4.3
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function _before(AcceptanceTester $I)
	{
		// Activate Plugin.
		$I->activateWooCommerceAndConvertKitPlugins($I);

		// Setup WooCommerce Plugin.
		$I->setupWooCommercePlugin($I);
	}

	/**
	 * Test that no button is displayed on the Integration Settings screen
	 * when the Integration is disabled.
	 * 
	 * @since 	1.4.3
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testNoButtonDisplayedWhenIntegrationDisabled(AcceptanceTester $I)
	{
		// Disable the Integration.
		$I->loadConvertKitSettingsScreen($I);
		$I->checkOption('#woocommerce_ckwc_enabled');
		$I->fillField('woocommerce_ckwc_api_key', '');
		$I->fillField('woocommerce_ckwc_api_secret', '');
		$I->uncheckOption('#woocommerce_ckwc_enabled');
		$I->click('Save changes');

		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			false, // Form to subscribe email address to (not used)
			false, // Don't define a subscribe Event
			false // Don't send purchase data to ConvertKit
		);

		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		// Confirm that no Sync Past Order button is displayed.
		$I->dontSeeElementInDOM('a#ckwc_sync_past_orders');

		// Delete the Product and Order.
		$I->dontHavePostInDatabase(['ID' => $result['product_id']]);
		$I->dontHavePostInDatabase(['ID' => $result['order_id']]);
	}

	/**
	 * Test that no button is displayed on the Integration Settings screen
	 * when the Integration is enabled, and no API credentials are specified.
	 * 
	 * @since 	1.4.3
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testNoButtonDisplayedWhenIntegrationEnabledWithNoAPICredentials(AcceptanceTester $I)
	{
		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		// Enable integration.
		$I->checkOption('#woocommerce_ckwc_enabled');
		$I->fillField('woocommerce_ckwc_api_key', '');
		$I->fillField('woocommerce_ckwc_api_secret', '');

		// Save changes.
		$I->click('Save changes');

		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			false, // Form to subscribe email address to (not used)
			false, // Don't define a subscribe Event
			false // Don't send purchase data to ConvertKit
		);

		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		// Confirm that no Sync Past Order button is displayed.
		$I->dontSeeElementInDOM('a#ckwc_sync_past_orders');

		// Delete the Product and Order.
		$I->dontHavePostInDatabase(['ID' => $result['product_id']]);
		$I->dontHavePostInDatabase(['ID' => $result['order_id']]);
	}

	/**
	 * Test that no button is displayed on the Integration Settings screen
	 * when the Integration is enabled, valid API credentials are specified
	 * but there are no WooCommerce Orders.
	 * 
	 * @since 	1.4.3
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testNoButtonDisplayedWhenNoOrders(AcceptanceTester $I)
	{
		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);

		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		// Confirm that no Sync Past Order button is displayed.
		$I->dontSeeElementInDOM('a#ckwc_sync_past_orders');
	}

	/**
	 * Test that no button is displayed on the Integration Settings screen
	 * when:
	 * - the Integration is enabled,
	 * - valid API credentials are specified,
	 * - a WooCommerce Order exists, that has had its Purchase Data sent to ConvertKit.
	 * 
	 * @since 	1.4.3
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testNoButtonDisplayedWhenNoPastOrders(AcceptanceTester $I)
	{
		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);

		// Create Product and Checkout for this test, sending the Order
		// to ConvertKit.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			false, // Form to subscribe email address to (not used)
			false, // Don't define a subscribe Event
			true // Send purchase data to ConvertKit
		);

		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		// Confirm that no Sync Past Order button is displayed.
		$I->dontSeeElementInDOM('a#ckwc_sync_past_orders');

		// Delete the Product and Order.
		$I->dontHavePostInDatabase(['ID' => $result['product_id']]);
		$I->dontHavePostInDatabase(['ID' => $result['order_id']]);
	}

	/**
	 * Test that a button is displayed on the Integration Settings screen
	 * when:
	 * - the Integration is enabled,
	 * - valid API credentials are specified,
	 * - a WooCommerce Order exists, that has not had its Purchase Data sent to ConvertKit,
	 * - clicking the button sends the Order to ConvertKit.
	 * 
	 * @since 	1.4.3
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSyncPastOrder(AcceptanceTester $I)
	{
		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);

		// Create Product and Checkout for this test, not sending the Order
		// to ConvertKit.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			false, // Form to subscribe email address to (not used)
			false, // Don't define a subscribe Event
			false // Don't send purchase data to ConvertKit
		);

		// Login as the Administrator
		$I->loginAsAdmin();

		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		// Confirm that the Sync Past Order button is displayed.
		$I->seeElementInDOM('a#ckwc_sync_past_orders');

		// Click the button.
		$I->click('a#ckwc_sync_past_orders');

		// Confirm the popup.
		$I->acceptPopup();

		// Wait a few seconds for the API call to be made.
		$I->wait(5);

		// Confirm that the log shows a success message.
		$I->seeInSource('WooCommerce Order ID #' . $result['order_id'] . ' added to ConvertKit Purchase Data successfully.');

		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Confirm that the Cancel Sync button is disabled.
		$I->seeElementInDOM('a.cancel[disabled]');

		// Click the Return to Settings button.
		$I->click('Return to Settings');

		// Confirm that the Settings screen is displayed.
		$I->seeInSource('Enable ConvertKit integration');

		// Delete the Product and Order.
		$I->dontHavePostInDatabase(['ID' => $result['product_id']]);
		$I->dontHavePostInDatabase(['ID' => $result['order_id']]);
	}

	/**
	 * Test that a WooCommerce Order, that has not had its Purchase Data sent to ConvertKit,
	 * is not sent to ConvertKit when the Sync Past Orders button is clicked and the API
	 * credentials are invalid.
	 * 
	 * @since 	1.4.3
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSyncPastOrderWithInvalidAPICredentials(AcceptanceTester $I)
	{
		// Enable the Integration and define invalid API Credentials.
		$I->loadConvertKitSettingsScreen($I);
		$I->checkOption('#woocommerce_ckwc_enabled');
		$I->fillField('woocommerce_ckwc_api_key', 'invalidApiKey');
		$I->fillField('woocommerce_ckwc_api_secret', 'invalidApiSecret');
		$I->click('Save changes');

		// Create Product and Checkout for this test, not sending the Order
		// to ConvertKit.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			false, // Form to subscribe email address to (not used)
			false, // Don't define a subscribe Event
			false // Don't send purchase data to ConvertKit
		);

		// Login as the Administrator
		$I->loginAsAdmin();

		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		// Confirm that the Sync Past Order button is displayed.
		$I->seeElementInDOM('a#ckwc_sync_past_orders');

		// Click the button.
		$I->click('a#ckwc_sync_past_orders');

		// Confirm the popup.
		$I->acceptPopup();

		// Wait a few seconds for the API call to be made.
		$I->wait(5);

		// Confirm that the log shows a success message.
		$I->seeInSource('1/1: Response Error: Authorization Failed: API Key not valid');

		// Cancel sync.
		$I->click('Cancel');

		// Wait a few seconds for the current request to complete.
		$I->wait(5);

		// Confirm that the Cancel Sync button is disabled.
		$I->seeElementInDOM('a.cancel[disabled]');

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Delete the Product and Order.
		$I->dontHavePostInDatabase(['ID' => $result['product_id']]);
		$I->dontHavePostInDatabase(['ID' => $result['order_id']]);
	}
}