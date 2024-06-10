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
		// Setup ConvertKit Plugin's settings with an API Key and Secret.
		$I->haveOptionInDatabase(
			'_wp_convertkit_settings',
			[
				'api_key'         => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret'      => $_ENV['CONVERTKIT_API_SECRET'],
				'debug'           => 'on',
				'no_scripts'      => '',
				'no_css'          => '',
				'post_form'       => $_ENV['CONVERTKIT_API_FORM_ID'],
				'page_form'       => $_ENV['CONVERTKIT_API_FORM_ID'],
				'product_form'    => $_ENV['CONVERTKIT_API_FORM_ID'],
				'non_inline_form' => '',
			]
		);

		// Define an installation version older than 1.8.0.
		$I->haveOptionInDatabase('ckwc_version', '1.4.0');

		// Activate the Plugin, as if we just upgraded to 1.8.0 or higher.
		$I->activateConvertKitPlugin($I);

		// Confirm the options table now contains an Access Token and Refresh Token.
		$settings = $I->grabOptionFromDatabase('_wp_convertkit_settings');
		$I->assertArrayHasKey('access_token', $settings);
		$I->assertArrayHasKey('refresh_token', $settings);
		$I->assertArrayHasKey('token_expires', $settings);

		// Confirm the API Key and Secret are retained, in case we need them in the future.
		$I->assertArrayHasKey('api_key', $settings);
		$I->assertArrayHasKey('api_secret', $settings);
		$I->assertEquals($settings['api_key'], $_ENV['CONVERTKIT_API_KEY']);
		$I->assertEquals($settings['api_secret'], $_ENV['CONVERTKIT_API_SECRET']);

		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsGeneralScreen($I);

		// Confirm the Plugin authorized by checking for a Disconnect button.
		$I->see('ConvertKit WordPress');
		$I->see('Disconnect');

		// Check the order of the Form resources are alphabetical, with 'None' as the first choice.
		$I->checkSelectFormOptionOrder(
			$I,
			'#_wp_convertkit_settings_page_form',
			[
				'None',
			]
		);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateConvertKitPlugin($I);
		$I->resetConvertKitPlugin($I);
	}
}
