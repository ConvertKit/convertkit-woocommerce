<?php
/**
 * Tests various setting combinations for the "Display Opt-In Checkbox" and associated
 * options.
 * 
 * @since 	1.9.6
 */
class SettingAPIKeyAndSecretCest
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
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen when the Save Changes
	 * button is pressed and no settings are specified at WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSaveBlankSettings(AcceptanceTester $I)
	{
		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		// Click the Save Changes button.
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that a message is displayed telling the user that the API Credentials are invalid.
		$I->seeInSource('Please provide a valid ConvertKit API Key.');
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen,
	 * and no warning is displayed that the supplied API credentials are invalid, when
	 * saving valid API credentials at WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSaveValidAPICredentials(AcceptanceTester $I)
	{
		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);

		// Confirm that no message is displayed telling the user that the API Credentials are invalid.
		$I->dontSeeInSource('Please provide a valid ConvertKit API Key.');

		// Confirm that the Subscription dropdown option is displayed.
		$I->seeElement('#woocommerce_ckwc_subscription');

		// Confirm that an expected option can be selected.
		$I->selectOption('#woocommerce_ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen,
	 * and a warning is displayed that the supplied API credentials are invalid, when
	 * saving invalid API credentials at WooCommerce > Settings > Integration > ConvertKit.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSaveInvalidAPICredentials(AcceptanceTester $I)
	{
		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		// Complete API Fields.
		$I->fillField('woocommerce_ckwc_api_key', 'fakeApiKey');
		$I->fillField('woocommerce_ckwc_api_secret', 'fakeApiSecret');

		// Click the Save Changes button.
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the value of the fields match the inputs provided.
		$I->seeInField('woocommerce_ckwc_api_key', 'fakeApiKey');
		$I->seeInField('woocommerce_ckwc_api_secret', 'fakeApiSecret');

		// Confirm that a message is displayed telling the user that the API Credentials are invalid.
		$I->seeInSource('Please provide a valid ConvertKit API Key.');
	}
}
