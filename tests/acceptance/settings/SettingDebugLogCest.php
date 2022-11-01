<?php
/**
 * Tests various setting combinations for the "Debug" options.
 *
 * @since   1.4.2
 */
class SettingDebugLogCest
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
		$I->activateWooCommerceAndConvertKitPlugins($I);

		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);
	}

	/**
	 * Test that debug logging works when enabled at
	 * WooCommerce > Settings > Integration > ConvertKit.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester
	 */
	public function testDebugEnabled(AcceptanceTester $I)
	{
		// Check "Debug" checkbox.
		$I->checkOption('#woocommerce_ckwc_debug');

		// Save
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeCheckboxIsChecked('#woocommerce_ckwc_debug');

		// Load WooCommerce's Logs screen.
		$I->amOnAdminPage('admin.php?page=wc-status&tab=logs');

		// Confirm that a ConvertKit Log File exists in the dropdown selection of logs.
		$I->seeInSource('<option value="convertkit-' . date('Y-m-d'));
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
