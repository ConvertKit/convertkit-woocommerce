<?php

class AnyErrorsOnBlankInstallCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function _before(AcceptanceTester $I)
	{
	}

	/**
	 * Check that no PHP errors or notices are displayed at WooCommerce > Settings > Integration > ConvertKit, when the Plugin is activated
	 * and not configured.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSettingsScreen(AcceptanceTester $I)
	{
		// Activate Plugin.
		$I->activateWooCommerceAndConvertKitPlugins($I);

		// Go to the Plugin's Settings > General Screen.
		$I->loadConvertKitSettingsScreen($I);
	}

	/**
	 * Check that no errors are displayed on Products > Add New, when the Plugin is activated
	 * and not configured.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testAddNewProduct(AcceptanceTester $I)
	{
		// Activate Plugin.
		$I->activateWooCommerceAndConvertKitPlugins($I);
		
		// Navigate to Products > Add New
		$I->amOnAdminPage('post-new.php?post_type=product');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}
}