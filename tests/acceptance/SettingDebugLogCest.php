<?php
/**
 * Tests various setting combinations for the "Debug" options.
 * 
 * @since 	1.9.6
 */
class SettingDebugLogCest
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
	 * Test that debug logging works when enabled at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testDebugEnabled(AcceptanceTester $I)
	{
		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);

		// Check "Send purchase data to ConvertKit" checkbox.
		$I->checkOption('#woocommerce_ckwc_debug');

		// Save
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeCheckboxIsChecked('#woocommerce_ckwc_debug');

		// @TODO Test debug log exists

	}

	/**
	 * Test that purchase data is not sent to ConvertKit when disabled at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testDebugDisabled(AcceptanceTester $I)
	{
		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);

		// Uncheck "Send purchase data to ConvertKit" checkbox.
		$I->uncheckOption('#woocommerce_ckwc_debug');

		// Save
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->dontSeeCheckboxIsChecked('#woocommerce_ckwc_debug');

		// @TODO Test debug log exists

	}

}