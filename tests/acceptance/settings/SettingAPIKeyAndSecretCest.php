<?php
/**
 * Tests OAuth connection and disconnection on the settings screen.
 *
 * @since   1.4.2
 */
class SettingOAuthCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->activateWooCommerceAndConvertKitPlugins($I);
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen
	 * and a Connect button is displayed when no credentials exist.
	 *
	 * @since   1.8.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testNoCredentials(AcceptanceTester $I)
	{
		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		// Confirm CSS and JS is output by the Plugin.
		$I->seeCSSEnqueued($I, 'convertkit-woocommerce/resources/backend/css/settings.css', 'ckwc-settings-css' );
		$I->seeCSSEnqueued($I, 'convertkit-woocommerce/resources/backend/css/select2.css', 'ckwc-admin-select2-css' );
		$I->seeJSEnqueued($I, 'convertkit-woocommerce/resources/backend/js/select2.js' );
		$I->seeJSEnqueued($I, 'convertkit-woocommerce/resources/backend/js/integration.js' );

		// Confirm no option is displayed to save changes, as the Plugin isn't authenticated.
		$I->dontSeeElementInDOM('button.woocommerce-save-button');

		// Confirm the Connect button displays.
		$I->see('Connect');
		$I->dontSee('Disconnect');

		// Check that a link to the OAuth auth screen exists and includes the state parameter.
		$I->seeInSource('<a href="https://app.convertkit.com/oauth/authorize?client_id=' . $_ENV['CONVERTKIT_OAUTH_CLIENT_ID'] . '&amp;response_type=code&amp;redirect_uri=' . urlencode( $_ENV['CONVERTKIT_OAUTH_REDIRECT_URI'] ) );
		$I->seeInSource('&amp;state=' . urlencode( $_ENV['TEST_SITE_WP_URL'] . '/wp-admin/admin.php?page=wc-settings&tab=integration&section=ckwc' ) );

		// Click the connect button.
		$I->click('Connect');

		// Confirm the ConvertKit hosted OAuth login screen is displayed.
		$I->waitForElementVisible('body.sessions');
		$I->seeInSource('oauth/authorize?client_id=' . $_ENV['CONVERTKIT_OAUTH_CLIENT_ID']);
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen,
	 * and a warning is displayed that the supplied credentials are invalid, when
	 * e.g. the access token has been revoked.
	 *
	 * @since   1.8.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testInvalidCredentials(AcceptanceTester $I)
	{
		$I->markTestIncomplete();

		// Setup Plugin.
		$I->setupConvertKitPlugin(
			$I,
			[
				'access_token'  => 'fakeAccessToken',
				'refresh_token' => 'fakeRefreshToken',
			]
		);

		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		$I->see('XXX');

		// Confirm the Connect button displays.
		$I->see('Connect');
		$I->dontSee('Disconnect');
		$I->dontSeeElementInDOM('button.woocommerce-save-button');

		// Navigate to the WordPress Admin.
		$I->amOnAdminPage('index.php');

		// Check that a notice is displayed that the API credentials are invalid.
		$I->seeErrorNotice($I, 'ConvertKit: Authorization failed. Please connect your ConvertKit account.');
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen,
	 * when valid credentials exist.
	 *
	 * @since   1.8.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testValidCredentials(AcceptanceTester $I)
	{
		// Setup Plugin.
		$I->setupConvertKitPlugin($I);
		$I->setupConvertKitPluginResources($I);

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

		// Confirm that an expected option can be selected.
		$I->selectOption('#woocommerce_ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Disconnect the Plugin connection to ConvertKit.
		$I->click('Disconnect');

		// Confirm the Connect button displays.
		$I->see('Connect');
		$I->dontSee('Disconnect');
		$I->dontSeeElementInDOM('button.woocommerce-save-button');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.4.4
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateWooCommerceAndConvertKitPlugins($I);
		$I->resetConvertKitPlugin($I);
	}
}
