<?php
/**
 * Tests the Refresh Resource button, which are displayed next to settings fields
 * when editing a WooCommerce Product.
 * 
 * @since 	1.4.8
 */
class RefreshResourcesButtonCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 * 
	 * @since 	1.4.8
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function _before(AcceptanceTester $I)
	{
		// Activate and Setup ConvertKit plugin using API keys that have no resources (forms, sequences, tags).
		$I->activateWooCommerceAndConvertKitPlugins($I);
		$I->setupConvertKitPlugin($I, $_ENV['CONVERTKIT_API_KEY_NO_DATA'], $_ENV['CONVERTKIT_API_SECRET_NO_DATA']);

		// Change API keys in database to ones that have ConvertKit Resources.
		// We do this directly vs. via the settings screen, so that the Plugin's cached resources remain blank
		// until a refresh button is clicked.
		$I->haveOptionInDatabase('woocommerce_ckwc_settings', [
			'enabled'	 => 'yes',
			'api_key'    => $_ENV['CONVERTKIT_API_KEY'],
			'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
			'debug'      => 'yes',
		]);
	}

	/**
	 * Test that the refresh button for works when adding a new Product.
	 * 
	 * @since 	1.4.8
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testRefreshResourcesOnProduct(AcceptanceTester $I)
	{
		// Navigate to Product > Add New
		$I->amOnAdminPage('post-new.php?post_type=product');

		// Click the refresh button.
		$I->click('button.ckwc-refresh-resources');

		// Wait for button to change its state from disabled.
		$I->waitForElementVisible('button.ckwc-refresh-resources:not(:disabled)');

		// Change resource to value specified in the .env file, which should now be available.
		// If the expected dropdown value does not exist in the Select2 field, this will fail the test.
		$I->fillSelect2Field($I, '#select2-ckwc_subscription-container', $_ENV['CONVERTKIT_API_FORM_NAME']);
	}

	/**
	 * Test that the refresh buttons for Forms, Sequences and Tags works when Quick Editing a WooCommerce Product.
	 * 
	 * @since 	1.4.8
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testRefreshResourcesOnQuickEdit(AcceptanceTester $I)
	{
		// Programmatically create a Product.
		$pageID = $I->havePostInDatabase([
			'post_type' 	=> 'product',
			'post_title' 	=> 'ConvertKit: Product: Refresh Resources: Quick Edit',
		]);

		// Open Quick Edit form for the Product in the WooCommerce Products WP_List_Table.
		$I->openQuickEdit($I, 'product', $pageID);

		// Click the refresh button.
		$I->waitForElementVisible('#ckwc-quick-edit button.ckwc-refresh-resources');
		$I->click('#ckwc-quick-edit button.ckwc-refresh-resources');

		// Wait for button to change its state from disabled.
		$I->waitForElementVisible('#ckwc-quick-edit button.ckwc-refresh-resources:not(:disabled)');

		// Change resource to value specified in the .env file, which should now be available.
		// If the expected dropdown value does not exist, this will fail the test.
		$I->selectOption('#ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);
	}

	/**
	 * Test that the refresh buttons for Forms, Sequences and Tags works when Bulk Editing WooCommerce Products.
	 * 
	 * @since 	1.4.8
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testRefreshResourcesOnBulkEdit(AcceptanceTester $I)
	{
		// Programmatically create two Pages.
		$productIDs = array(
			$I->havePostInDatabase([
				'post_type' 	=> 'product',
				'post_title' 	=> 'ConvertKit: Product: Refresh Resources: Bulk Edit #1',
			]),
			$I->havePostInDatabase([
				'post_type' 	=> 'product',
				'post_title' 	=> 'ConvertKit: Product: Refresh Resources: Bulk Edit #2',
			])
		);

		// Open Bulk Edit form for the Products in the WooCommerce Products WP_List_Table.
		$I->openBulkEdit($I, 'product', $productIDs);

		// Click the refresh button.
		$I->waitForElementVisible('#ckwc-bulk-edit button.ckwc-refresh-resources');
		$I->click('#ckwc-bulk-edit button.ckwc-refresh-resources');

		// Wait for button to change its state from disabled.
		$I->waitForElementVisible('#ckwc-bulk-edit button.ckwc-refresh-resources:not(:disabled)');

		// Change resource to value specified in the .env file, which should now be available.
		// If the expected dropdown value does not exist, this will fail the test.
		$I->selectOption('#ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 * 
	 * @since 	1.4.8
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateWooCommerceAndConvertKitPlugins($I);
		$I->resetConvertKitPlugin($I);
	}
}