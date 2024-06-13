<?php
/**
 * Tests edge cases and upgrade routines when upgrading between specific ConvertKit Plugin versions.
 *
 * @since   1.8.0
 */
class UpgradePathsCest
{
	/**
	 * Tests that an Access Token and Refresh Token are obtained using an API Key and Secret
	 * when upgrading to 1.8.0 or later.
	 *
	 * @since   1.8.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testGetAccessTokenByAPIKeyAndSecret(AcceptanceTester $I)
	{
		// Setup Plugin's settings with an API Key and Secret.
		$I->haveOptionInDatabase(
			'woocommerce_ckwc_settings',
			[
				'api_key'    => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
				'enabled'    => 'yes',
			]
		);

		// Define an installation version older than 1.8.0.
		$I->haveOptionInDatabase('ckwc_version', '1.4.0');

		// Activate the Plugin, as if we just upgraded to 1.8.0 or higher.
		$I->activateWooCommerceAndConvertKitPlugins($I);

		// Confirm the options table now contains an Access Token and Refresh Token.
		$settings = $I->grabOptionFromDatabase('woocommerce_ckwc_settings');
		$I->assertArrayHasKey('access_token', $settings);
		$I->assertArrayHasKey('refresh_token', $settings);
		$I->assertArrayHasKey('token_expires', $settings);

		// Confirm the API Key and Secret are retained, in case we need them in the future.
		$I->assertArrayHasKey('api_key', $settings);
		$I->assertArrayHasKey('api_secret', $settings);
		$I->assertEquals($settings['api_key'], $_ENV['CONVERTKIT_API_KEY']);
		$I->assertEquals($settings['api_secret'], $_ENV['CONVERTKIT_API_SECRET']);

		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		// Confirm the Disconnect and Save Changes buttons display.
		$I->see('Disconnect');
		$I->seeElementInDOM('button.woocommerce-save-button');

		// Enable the Integration.
		$I->checkOption('#woocommerce_ckwc_enabled');

		// Confirm that the Subscription dropdown option is displayed.
		$I->seeElement('#woocommerce_ckwc_subscription');

		// Check the order of the resource dropdown are alphabetical.
		$I->checkSelectWithOptionGroupsOptionOrder($I, '#woocommerce_ckwc_subscription');

		// Save changes (avoids a JS alert box which would prevent other tests from running due to changes made on screen).
		$I->click('Save changes');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.8.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateWooCommerceAndConvertKitPlugins($I);
		$I->resetConvertKitPlugin($I);
	}
}
