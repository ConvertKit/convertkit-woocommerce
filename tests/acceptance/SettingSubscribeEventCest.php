<?php
/**
 * Tests various setting combinations across the following settings:
 * - Subscribe Event
 * - Display Opt-In Checkbox
 * - API Keys
 * - Subscription Form
 * 
 * @since 	1.4.2
 */
class SettingSubscribeEventsCest
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
		// Activate Plugin.
		$I->activateWooCommerceAndConvertKitPlugins($I);

		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);
	}

	/**
	 * Test that the Order Created option is saved when selected at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOrderCreatedWithoutOptInCheckbox(AcceptanceTester $I)
	{
		// Set Subscribe Event = Order Created.
		$I->selectOption('#woocommerce_ckwc_event', 'Order Created');

		// Save.
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#woocommerce_ckwc_event', 'Order Created');
			
	}
	/**
	 * Test that the Order Processing option is saved when selected at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOrderProcessing(AcceptanceTester $I)
	{
		// Set Subscribe Event = Order Processing.
		$I->selectOption('#woocommerce_ckwc_event', 'Order Processing');

		// Save.
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#woocommerce_ckwc_event', 'Order Processing');
	}

	/**
	 * Test that the Order Completed option is saved when selected at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOrderCompleted(AcceptanceTester $I)
	{
		// Set Subscribe Event = Order Completed.
		$I->selectOption('#woocommerce_ckwc_event', 'Order Completed');

		// Save.
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#woocommerce_ckwc_event', 'Order Completed');
	}
}
