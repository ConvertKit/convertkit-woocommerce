<?php

class ActivateDeactivatePluginCest
{
	/**
	 * Activate the Plugin and confirm a success notification
	 * is displayed with no errors.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testPluginActivation(AcceptanceTester $I)
	{
		$I->activateWooCommerceAndConvertKitPlugins($I);
	}

	/**
	 * Activate the Plugin without the WooCommerce Plugin and confirm a success notification
	 * is displayed with no errors.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testPluginActivationWithoutWooCommerce(AcceptanceTester $I)
	{
		$I->activateConvertKitPlugin($I);
	}

	/**
	 * Deactivate the Plugin and confirm a success notification
	 * is displayed with no errors.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testPluginDeactivation(AcceptanceTester $I)
	{
		$I->deactivateConvertKitPlugin($I);
	}
}