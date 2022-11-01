<?php
/**
 * Tests various setting combinations for the "Name Format" option.
 *
 * @since   1.4.2
 */
class SettingNameFormatCest
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

		// Setup WooCommerce Plugin.
		$I->setupWooCommercePlugin($I);

		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);
	}

	/**
	 * Test that the Billing First Name option is saved when selected at
	 * WooCommerce > Settings > Integration > ConvertKit, and honored
	 * when submitted to ConvertKit.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester
	 */
	public function testNameFormatBillingFirstName(AcceptanceTester $I)
	{
		// Set Name Format = Billing First Name
		$I->selectOption('#woocommerce_ckwc_name_format', 'Billing First Name');

		// Save
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#woocommerce_ckwc_name_format', 'Billing First Name');

		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			true, // Display Opt-In checkbox on Checkout
			true, // Check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to
			'Order Pending payment', // Subscribe on WooCommerce "Order Pending payment" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was now added to ConvertKit.
		$I->apiCheckSubscriberExists($I, $result['email_address']);

		// Confirm that the subscriber's name = First
		$I->apiCheckSubscriberEmailAndNameExists($I, $result['email_address'], 'First');

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Billing Last Name option is saved when selected at
	 * WooCommerce > Settings > Integration > ConvertKit, and honored
	 * when submitted to ConvertKit.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester
	 */
	public function testNameFormatBillingLastName(AcceptanceTester $I)
	{
		// Set Name Format = Billing Last Name
		$I->selectOption('#woocommerce_ckwc_name_format', 'Billing Last Name');

		// Save
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#woocommerce_ckwc_name_format', 'Billing Last Name');

		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			true, // Display Opt-In checkbox on Checkout
			true, // Check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to
			'Order Pending payment', // Subscribe on WooCommerce "Order Pending payment" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was now added to ConvertKit.
		$I->apiCheckSubscriberExists($I, $result['email_address']);

		// Confirm that the subscriber's name = Last
		$I->apiCheckSubscriberEmailAndNameExists($I, $result['email_address'], 'Last');

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Billing First Name + Billing Last Name option is saved when selected at
	 * WooCommerce > Settings > Integration > ConvertKit, and honored
	 * when submitted to ConvertKit.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester
	 */
	public function testNameFormatBillingFirstNameAndLastName(AcceptanceTester $I)
	{
		// Set Name Format = Billing First Name
		$I->selectOption('#woocommerce_ckwc_name_format', 'Billing First Name + Billing Last Name');

		// Save
		$I->click('Save changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#woocommerce_ckwc_name_format', 'Billing First Name + Billing Last Name');

		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			true, // Display Opt-In checkbox on Checkout
			true, // Check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to
			'Order Pending payment', // Subscribe on WooCommerce "Order Pending payment" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was now added to ConvertKit.
		$I->apiCheckSubscriberExists($I, $result['email_address']);

		// Confirm that the subscriber's name = First Last
		$I->apiCheckSubscriberEmailAndNameExists($I, $result['email_address'], 'First Last');

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
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
