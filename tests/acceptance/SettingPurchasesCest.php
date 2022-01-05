<?php
/**
 * Tests various setting combinations for the "Purchases" options.
 * 
 * @since 	1.4.2
 */
class SettingPurchasesCest
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
		$I->activateWooCommerceAndConvertKitPlugins($I);
	}

	/**
	 * Test that purchase data is sent to ConvertKit when enabled at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataEnabled(AcceptanceTester $I)
	{
		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);

		// Check "Send purchase data to ConvertKit" checkbox.
		$I->checkOption('#woocommerce_ckwc_send_purchases');

		// Save
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeCheckboxIsChecked('#woocommerce_ckwc_send_purchases');
	}

	/**
	 * Test that purchase data is not sent to ConvertKit when disabled at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataDisabled(AcceptanceTester $I)
	{
		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);

		// Uncheck "Send purchase data to ConvertKit" checkbox.
		$I->uncheckOption('#woocommerce_ckwc_send_purchases');

		// Save
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->dontSeeCheckboxIsChecked('#woocommerce_ckwc_send_purchases');
	}
}