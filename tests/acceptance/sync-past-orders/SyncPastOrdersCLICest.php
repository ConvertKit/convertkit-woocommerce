<?php
/**
 * Tests for Sync Past Orders functionality using WP-CLI
 *
 * @since   1.7.1
 */
class SyncPastOrdersCLICest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.7.1
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _before(AcceptanceTester $I)
	{
		// Activate Plugin.
		$I->activateWooCommerceAndConvertKitPlugins($I);

		// Enable HPOS.
		$I->setupWooCommerceHPOS($I);

		// Setup WooCommerce Plugin.
		$I->setupWooCommercePlugin($I);

		// Activate Custom Order Numbers Plugin.
		$I->activateThirdPartyPlugin($I, 'custom-order-numbers-for-woocommerce');

		// Setup Custom Order Numbers Plugin.
		$I->setupCustomOrderNumbersPlugin($I);

		// Delete all existing WooCommerce Orders from the database.
		$I->wooCommerceDeleteAllOrders($I);
	}

	/**
	 * Test that the CLI command returns the expected console output when
	 * attempting to sync past orders to ConvertKit Purchase Data, and no
	 * WooCommerce Orders exist.
	 *
	 * @since   1.7.1
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSyncPastOrdersWhenNoOrdersExist(AcceptanceTester $I)
	{
		$I->cli('ckwc-sync-past-orders');
		var_dump($I->grabLastShellOutput());
		die();
		$I->seeInShellOutput('No outstanding Orders to send to ConvertKit');
	}

	/**
	 * Test that the CLI command returns the expected console output when
	 * attempting to sync past orders to ConvertKit Purchase Data, and
	 * WooCommerce Orders exist.
	 *
	 * @since   1.7.1
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSyncPastOrders(AcceptanceTester $I)
	{
		// Create Product and Customer.
		$productID = $I->wooCommerceCreateSimpleProduct($I);
		$email     = $I->generateEmailAddress();
		$userID    = $I->haveUserInDatabase(
			'sync-past-orders',
			'subscriber',
			[
				'user_email' => $email,
			]
		);

		// Create Order using this Product.
		$orderID = $I->wooCommerceOrderCreate($I, $productID, $userID);

		// Run CLI command.
		$I->cli('ckwc-sync-past-orders');
		$I->seeInShellOutput('WooCommerce Order ID #' . $orderID . 'added to ConvertKit Purchase Data successfully. ConvertKit Purchase ID: #');

		// Confirm that the last Order was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $orderID, $email, $productID);
	}

	/**
	 * Test that the CLI command returns the expected console output when
	 * attempting to sync past orders to ConvertKit Purchase Data using
	 * the --limit argument, and WooCommerce Orders exist.
	 *
	 * @since   1.7.1
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSyncPastOrdersWithLimitArgument(AcceptanceTester $I)
	{
		// Create Product and Customer.
		$productID = $I->wooCommerceCreateSimpleProduct($I);
		$email     = $I->generateEmailAddress();
		$userID    = $I->haveUserInDatabase(
			'sync-past-orders',
			'subscriber',
			[
				'user_email' => $email,
			]
		);

		// Create Orders using this Product.
		$orderIDs = [
			$I->wooCommerceOrderCreate($I, $productID, $userID),
			$I->wooCommerceOrderCreate($I, $productID, $userID),
		];

		// Run CLI command with --limit=1 to send each Order individually.
		foreach ($orderIDs as $orderID) {
			// Run command.
			$I->cli('ckwc-sync-past-orders --limit=1');
			$I->seeInShellOutput('WooCommerce Order ID #' . $orderID . 'added to ConvertKit Purchase Data successfully. ConvertKit Purchase ID: #');

			// Confirm that the Order was added to ConvertKit.
			$I->apiCheckPurchaseExists($I, $orderID, $email, $productID);
		}
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.4.4
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateWooCommerceAndConvertKitPlugins($I);
		$I->deactivateThirdPartyPlugin($I, 'custom-order-numbers-for-woocommerce');
		$I->resetConvertKitPlugin($I);
	}
}
