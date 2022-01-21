<?php
/**
 * Tests that the ConvertKit Form / Tag / Sequence selection works on
 * a WooCommerce Product.
 * 
 * @since 	1.4.2
 */
class ProductCest
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

		// Setup WooCommerce Plugin.
		$I->setupWooCommercePlugin($I);
	}

	/**
	 * Test that the meta box displayed when adding/editing a Product does not
	 * output a field, and instead tells the user to configure the integration,
	 * when the integration is disabled.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testProductFieldsWithIntegrationDisabled(AcceptanceTester $I)
	{
		// Navigate to Products > Add New.
		$I->amOnAdminPage('post-new.php?post_type=product');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the ConvertKit meta box exists.
		$I->seeElementInDOM('#ckwc');

		// Check that the dropdown field to select a Form, Tag or Sequence is not displayed.
		$I->dontSeeElementInDOM('#ckwc_subscription');

		// Check that a message is displayed telling the user to enable the integration.
		$I->seeInSource('To configure the ConvertKit Form, Tag or Sequence to subscribe Customers to who purchase this Product');

		// Check that a link to the Plugin Settings exists.
		$I->seeInSource('<a href="' . $_ENV['TEST_SITE_WP_URL'] . '/wp-admin/admin.php?page=wc-settings&amp;tab=integration&amp;section=ckwc">enable the ConvertKit WooCommerce integration</a>');
	}

	/**
	 * Test that the meta box displayed when adding/editing a Product does not
	 * output a field, and instead tells the user to configure the integration,
	 * when the integration is enabled but no API Key is specified.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testProductFieldsWithIntegrationEnabledAndNoAPIKey(AcceptanceTester $I)
	{
		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		// Enable the Integration.
		$I->checkOption('#woocommerce_ckwc_enabled');

		// Click the Save Changes button.
		$I->click('Save changes');

		// Navigate to Products > Add New.
		$I->amOnAdminPage('post-new.php?post_type=product');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the ConvertKit meta box exists.
		$I->seeElementInDOM('#ckwc');

		// Check that the dropdown field to select a Form, Tag or Sequence is not displayed.
		$I->dontSeeElementInDOM('#ckwc_subscription');

		// Check that a message is displayed telling the user to enable the integration.
		$I->seeInSource('To configure the ConvertKit Form, Tag or Sequence to subscribe Customers to who purchase this Product');

		// Check that a link to the Plugin Settings exists.
		$I->seeInSource('<a href="' . $_ENV['TEST_SITE_WP_URL'] . '/wp-admin/admin.php?page=wc-settings&amp;tab=integration&amp;section=ckwc">enable the ConvertKit WooCommerce integration</a>');
	}

	/**
	 * Test that the meta box displayed when adding/editing a Product does not
	 * output PHP errors when the integration is enabled with an invalid API Key.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testProductFieldsWithIntegrationEnabledAndInvalidAPIKey(AcceptanceTester $I)
	{
		// Load Settings screen.
		$I->loadConvertKitSettingsScreen($I);

		// Enable the Integration.
		$I->checkOption('#woocommerce_ckwc_enabled');

		// Complete API Fields.
		$I->fillField('woocommerce_ckwc_api_key', 'fakeApiKey');
		$I->fillField('woocommerce_ckwc_api_secret', 'fakeApiSecret');

		// Click the Save Changes button.
		$I->click('Save changes');

		// Navigate to Products > Add New.
		$I->amOnAdminPage('post-new.php?post_type=product');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the ConvertKit meta box exists.
		$I->seeElementInDOM('#ckwc');

		// Check that the dropdown field to select a Form, Tag or Sequence is displayed.
		$I->seeElementInDOM('#ckwc_subscription');

		// Set dropdown field to a Form.
		$I->selectOption('#ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Save
		$I->click('Publish');

		// Wait until JS completes and redirects.
		$I->waitForElement('.notice-success', 30);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);
	}
}
