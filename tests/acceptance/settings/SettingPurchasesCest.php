<?php
/**
 * Tests various setting combinations for the "Purchases" options.
 *
 * @since   1.4.2
 */
class SettingPurchasesCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester
	 */
	public function _before(AcceptanceTester $I)
	{
		// Activate Plugin.
		$I->activateWooCommerceAndConvertKitPlugins($I);

		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);
	}

	/**
	 * Test that the Purchase Data option is saved when enabled at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester
	 */
	public function testSendPurchaseDataEnabled(AcceptanceTester $I)
	{
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
	 * Test that the Purchase Data option is saved when disabled at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester
	 */
	public function testSendPurchaseDataDisabled(AcceptanceTester $I)
	{
		// Uncheck "Send purchase data to ConvertKit" checkbox.
		$I->uncheckOption('#woocommerce_ckwc_send_purchases');

		// Save
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->dontSeeCheckboxIsChecked('#woocommerce_ckwc_send_purchases');
	}

	/**
	 * Test that the Purchase Data Event option is saved when set to Order Processing at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 *
	 * @since   1.4.5
	 *
	 * @param   AcceptanceTester $I  Tester
	 */
	public function testSendPurchaseDataEventOrderProcessing(AcceptanceTester $I)
	{
		// Set Event = Order Processing.
		$I->selectOption('#woocommerce_ckwc_send_purchases_event', 'Order Processing');

		// Save.
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#woocommerce_ckwc_send_purchases_event', 'Order Processing');
	}

	/**
	 * Test that the Purchase Data Event option is saved when set to Order Completed at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 *
	 * @since   1.4.5
	 *
	 * @param   AcceptanceTester $I  Tester
	 */
	public function testSendPurchaseDataEventOrderCompleted(AcceptanceTester $I)
	{
		// Set Event = Order Completed.
		$I->selectOption('#woocommerce_ckwc_send_purchases_event', 'Order Completed');

		// Save.
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#woocommerce_ckwc_send_purchases_event', 'Order Completed');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.4.4
	 *
	 * @param   AcceptanceTester $I  Tester
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateWooCommerceAndConvertKitPlugins($I);
		$I->resetConvertKitPlugin($I);
	}
}
