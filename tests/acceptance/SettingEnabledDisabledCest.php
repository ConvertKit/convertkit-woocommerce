<?php
/**
 * Tests for the Enable/Disable option on the WooCommerce Integration.
 * 
 * @since 	1.9.6
 */
class SettingEnabledDisabledCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->activateConvertKitPlugin($I);
	}

	/**
	 * Test that the integration doesn't perform any expected actions when disabled at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testIntegrationWhenDisabled(AcceptanceTester $I)
	{
		// Create Simple Product.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Add Product to Cart and load Checkout.
		$I->wooCommerceCheckoutWithProduct($I, $productID, 'Simple Product');
		
		// Click Place Order button.
		$I->click('#place_order');

		// Check that no WooCommerce, PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}
}