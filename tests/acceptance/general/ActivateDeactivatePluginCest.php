<?php
/**
 * Tests Plugin activation and deactivation.
 *
 * @since   1.4.2
 */
class ActivateDeactivatePluginCest
{
	/**
	 * Activate the Plugin and confirm a success notification
	 * is displayed with no errors.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testPluginActivationAndDeactivation(AcceptanceTester $I)
	{
		$I->activateWooCommerceAndConvertKitPlugins($I);
		$I->deactivateWooCommerceAndConvertKitPlugins($I);
	}

	/**
	 * Activate the Plugin without the WooCommerce Plugin and confirm a success notification
	 * is displayed with no errors.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testPluginActivationAndDeactivationWithoutWooCommerce(AcceptanceTester $I)
	{
		$I->activateConvertKitPlugin($I);
		$I->deactivateConvertKitPlugin($I);
	}
}
