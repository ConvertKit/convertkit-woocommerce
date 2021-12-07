<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
	/**
	 * Helper method to assert that there are non PHP errors, warnings or notices output
	 * 
	 * @since 	1.0.0
	 */
	public function checkNoWarningsAndNoticesOnScreen($I)
	{
		// Check that the <body> class does not have a php-error class, which indicates a suppressed PHP function call error.
		$I->dontSeeElement('.php-error');

		// Check that no Xdebug errors exist.
		$I->dontSeeElement('.xdebug-error');
		$I->dontSeeElement('.xe-notice');
	}

	/**
	 * Helper method to assert that the field's value contains the given value.
	 * 
	 * @since 	1.0.0
	 */
	public function seeFieldContains($I, $element, $value)
	{
		$this->assertNotFalse(strpos($I->grabValueFrom($element), $value));
	}

	/**
	 * Helper method to close the Gutenberg "Welcome to the block editor" dialog, which
	 * might show for each Page/Post test performed due to there being no persistence
	 * remembering that the user dismissed the dialog.
	 * 
	 * @since 	1.0.0
	 */
	public function maybeCloseGutenbergWelcomeModal($I)
	{
		try {
			$I->performOn('.components-modal__screen-overlay', [
				'click' => '.components-modal__screen-overlay .components-modal__header button.components-button'
			], 3);
		} catch ( \Facebook\WebDriver\Exception\TimeoutException $e ) {
		}
	}

	/**
	 * Helper method to activate the Plugin.
	 * 
	 * @since 	1.0.0
	 */
	public function activateConvertKitPlugin($I)
	{
		// Login as the Administrator
		$I->loginAsAdmin();

		// Go to the Plugins screen in the WordPress Administration interface.
		$I->amOnPluginsPage();

		// Activate the WooCommerce Plugin.
		$I->activatePlugin('woocommerce');

		// Activate the Plugin.
		$I->activatePlugin('convertkit-woocommerce-addon');

		// Check that the Plugin activated successfully.
		$I->seePluginActivated('convertkit-woocommerce-addon');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Flush Permalinks by visiting Settings > Permalinks, so that newly registered Post Types e.g.
		// WooCommerce Products work.
		$I->amOnAdminPage('options-permalink.php');

	}

	/**
	 * Helper method to deactivate the Plugin.
	 * 
	 * @since 	1.0.0
	 */
	public function deactivateConvertKitPlugin($I)
	{
		// Login as the Administrator
		$I->loginAsAdmin();

		// Go to the Plugins screen in the WordPress Administration interface.
		$I->amOnPluginsPage();

		// Deactivate the Plugin.
		$I->deactivatePlugin('convertkit-woocommerce-addon');

		// Check that the Plugin deactivated successfully.
		$I->seePluginDeactivated('convertkit-woocommerce-addon');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Flush Permalinks by visiting Settings > Permalinks, so that newly registered Post Types e.g.
		// WooCommerce Products work.
		$I->amOnAdminPage('options-permalink.php');
	}

	/**
	 * Helper method to load the WooCommerce > Settings > Integration > ConvertKit screen.
	 * 
	 * @since 	1.0.0
	 */
	public function loadConvertKitSettingsScreen($I)
	{
		$I->amOnAdminPage('admin.php?page=wc-settings&tab=integration&section=ckwc');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Helper method to setup the Plugin's API Key and Secret, and enable the integration.
	 * 
	 * @since 	1.0.0
	 */
	public function setupConvertKitPlugin($I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsScreen($I);

		// Enable the Integration.
		$I->checkOption('#woocommerce_ckwc_enabled');

		// Complete API Fields.
		$I->fillField('woocommerce_ckwc_api_key', $_ENV['CONVERTKIT_API_KEY']);
		$I->fillField('woocommerce_ckwc_api_secret', $_ENV['CONVERTKIT_API_SECRET']);

		// Click the Save Changes button.
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the value of the fields match the inputs provided.
		$I->seeCheckboxIsChecked('#woocommerce_ckwc_enabled');	
		$I->seeInField('woocommerce_ckwc_api_key', $_ENV['CONVERTKIT_API_KEY']);
		$I->seeInField('woocommerce_ckwc_api_secret', $_ENV['CONVERTKIT_API_SECRET']);
	}

	/**
	 * Helper method to setup the WooCommerce Plugin.
	 * 
	 * @since 	1.0.0
	 */
	public function setupWooCommercePlugin($I)
	{
		// Enable "Cash on Delivery" Payment Method.
		// If no Payment Method is enabled, WooCommerce Checkout tests will always fail.
		$I->amOnAdminPage('admin.php?page=wc-settings&tab=checkout&section=cod');
		$I->checkOption('#woocommerce_cod_enabled');
		$I->click('Save changes');
	}

	/**
	 * Creates a 'Simple product' in WooCommerce that can be used for tests.
	 * 
	 * @since 	1.0.0
	 * 
	 * @return 	int 	Product ID
	 */
	public function wooCommerceCreateSimpleProduct($I)
	{
		return $I->havePostInDatabase([
			'post_type'		=> 'product',
			'post_status'	=> 'publish',
			'post_name' 	=> 'simple-product',
			'post_title'	=> 'Simple Product',
			'post_content'	=> 'Simple Product Content',
			'meta_input' => [
				'_backorders' => 'no',
				'_download_expiry' => -1,
				'_download_limit' => -1,
				'_downloadable' => 'no',
				'_manage_stock' => 'no',
				'_price' => 10,
				'_product_version' => '5.9.0',
				'_regular_price' => 10,
				'_sold_individually' => 'no',
				'_stock' => null,
				'_stock_status' => 'instock',
				'_tax_class' => '',
				'_tax_status' => 'taxable',
				'_virtual' => 'no',
				'_wc_average_rating' => 0,
				'_wc_review_count' => 0,
			],
		]);
	}

	/**
	 * Adds the given Product ID to the Cart, loading the Checkout screen
	 * and prefilling the standard WooCommerce Billing Fields.
	 * 
	 * @since 	1.0.0
	 */
	public function wooCommerceCheckoutWithProduct($I, $productID, $productName, $emailAddress = 'wordpress@convertkit.com')
	{
		// Load the Product on the frontend site.
		$I->amOnPage('/?p=' . $productID );

		// Check that no WooCommerce, PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Add Product to Cart.
		$I->click('button[name=add-to-cart]');

		// View Cart.
		$I->click('.woocommerce-message a.button.wc-forward');

		// Check that no WooCommerce, PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the Product exists in the Cart.
		$I->seeInSource($productName);

		// Proceed to Checkout.
		$I->click('a.checkout-button');

		// Check that no WooCommerce, PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Complete Billing Details.
		$I->fillField('#billing_first_name', 'First');
		$I->fillField('#billing_last_name', 'Last');
		$I->fillField('#billing_address_1', 'Address Line 1');
		$I->fillField('#billing_city', 'City');
		$I->fillField('#billing_postcode', '12345');
		$I->fillField('#billing_phone', '123-123-1234');
		$I->fillField('#billing_email', $emailAddress);
	}

	/**
	 * Check the given email address exists as a subscriber.
	 * 
	 * @param 	AcceptanceTester $I 			AcceptanceTester
	 * @param 	string 			$emailAddress 	Email Address
	 */ 	
	public function apiCheckSubscriberExists($I, $emailAddress)
	{
		// Run request.
		$results = $this->apiRequest('subscribers', 'GET', [
			'email_address' => $emailAddress,
		]);

		// Check at least one subscriber was returned and it matches the email address.
		$I->assertGreaterThan(0, $results['total_subscribers']);
		$I->assertEquals($emailAddress, $results['subscribers'][0]['email_address']);
	}

	/**
	 * Check the given email address does not exists as a subscriber.
	 * 
	 * @param 	AcceptanceTester $I 			AcceptanceTester
	 * @param 	string 			$emailAddress 	Email Address
	 */ 	
	public function apiCheckSubscriberDoesNotExist($I, $emailAddress)
	{
		// Run request.
		$results = $this->apiRequest('subscribers', 'GET', [
			'email_address' => $emailAddress,
		]);

		// Check no subscribers are returned by this request.
		$I->assertEquals(0, $results['total_subscribers']);
	}

	/**
	 * Unsubscribes the given email address. Useful for clearing the API
	 * between tests.
	 * 
	 * @param 	string 			$emailAddress 	Email Address
	 */ 	
	public function apiUnsubscribe($emailAddress)
	{
		// Run request.
		$this->apiRequest('unsubscribe', 'PUT', [
			'email' => $emailAddress,
		]);
	}

	/**
	 * Sends a request to the ConvertKit API, typically used to read an endpoint to confirm
	 * that data in an Acceptance Test was added/edited/deleted successfully.
	 * 
	 * @param 	string 	$endpoint 	Endpoint
	 * @param 	string 	$method 	Method (GET|POST|PUT)
	 * @param 	array 	$params 	Endpoint Parameters
	 */
	public function apiRequest($endpoint, $method = 'GET', $params = array())
	{
		// Build query parameters.
		$params = array_merge($params, [
			'api_key' => $_ENV['CONVERTKIT_API_KEY'],
			'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
		]);

		// Send request.
		try {
			$client = new \GuzzleHttp\Client();
			$result = $client->request($method, 'https://api.convertkit.com/v3/' . $endpoint . '?' . http_build_query($params), [
				'headers' => [
					'Accept-Encoding' => 'gzip',
					'timeout'         => 5,
				],
			]);

			// Return JSON decoded response.
			return json_decode($result->getBody()->getContents(), true);
		} catch(\GuzzleHttp\Exception\ClientException $e) {
			return [];
		}
	}
}
