<?php
/**
 * Tests various setting combinations for the "Name Format" option.
 * 
 * @since 	1.9.6
 */
class SettingNameFormatCest
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
	 * Test that the Billing First Name option is saved when selected at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testNameFormatBillingFirstName(AcceptanceTester $I)
	{
		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);

		// Set Name Format = Billing First Name
		$I->selectOption('#woocommerce_ckwc_name_format', 'Billing First Name');

		// Save
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#woocommerce_ckwc_name_format', 'Billing First Name');
	}


	/**
	 * Test that the Billing Last Name option is saved when selected at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testNameFormatBillingLastName(AcceptanceTester $I)
	{
		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);

		// Set Name Format = Billing First Name
		$I->selectOption('#woocommerce_ckwc_name_format', 'Billing Last Name');

		// Save
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#woocommerce_ckwc_name_format', 'Billing Last Name');
	}

	/**
	 * Test that the Billing First Name + Billing Last Name option is saved when selected at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testNameFormatBillingFirstNameAndLastName(AcceptanceTester $I)
	{
		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);

		// Set Name Format = Billing First Name
		$I->selectOption('#woocommerce_ckwc_name_format', 'Billing First Name + Billing Last Name');

		// Save
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#woocommerce_ckwc_name_format', 'Billing First Name + Billing Last Name');
	}
}
