<?php
/**
 * Tests the various settings do (or do not) subscribe the customer to a ConvertKit Form
 * or Tag on the Order Processing event when an order is placed through WooCommerce.
 * 
 * @since 	1.9.6
 */
class SubscribeOnOrderProcessingEventCest
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
		// Activate Plugin.
		$I->activateConvertKitPlugin($I);

		// Setup WooCommerce Plugin.
		$I->setupWooCommercePlugin($I);

		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - The opt in checkbox is checked on the WooCommerce checkout, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is marked as processing.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenCheckedWithFormAndSimpleProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			true, // Display Opt-In checkbox on Checkout
			true, // Check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was now added to ConvertKit.
		$I->apiCheckSubscriberExists($I, $result['email_address']);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Customer is NOT subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - The opt in checkbox is unchecked on the WooCommerce checkout, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenUncheckedWithFormAndSimpleProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			true, // Display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was not added to ConvertKit.
		$I->apiCheckSubscriberDoesNotExist($I, $result['email_address']);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is disabled in the integration Settings, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInDisabledWithFormAndSimpleProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was added to ConvertKit.
		$I->apiCheckSubscriberExists($I, $result['email_address']);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Customer is not subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - No Form is selected in the integration Settings, and
	 * - The opt in checkbox is checked on the WooCommerce checkout, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is marked as processing.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenCheckedWithNoFormAndSimpleProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			true, // Display Opt-In checkbox on Checkout
			true, // Check Opt-In checkbox on Checkout
			'Select a subscription option...', // Don't select a Form to subscribe the email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was still not added to ConvertKit.
		$I->apiCheckSubscriberDoesNotExist($I, $result['email_address']);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Customer is NOT subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - No Form is selected in the integration Settings, and
	 * - The opt in checkbox is unchecked on the WooCommerce checkout, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenUncheckedWithNoFormAndSimpleProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			true, // Display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			'Select a subscription option...', // Don't select a Form to subscribe the email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was still not added to ConvertKit.
		$I->apiCheckSubscriberDoesNotExist($I, $result['email_address']);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is disabled in the integration Settings, and
	 * - No Form is selected in the integration Settings, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInDisabledWithNoFormAndSimpleProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			'Select a subscription option...', // Don't select a Form to subscribe the email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was still not added to ConvertKit.
		$I->apiCheckSubscriberDoesNotExist($I, $result['email_address']);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - The opt in checkbox is checked on the WooCommerce checkout, and
	 * - The Customer purchases a 'virtual' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is marked as processing.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenCheckedWithFormAndVirtualProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'virtual', // Virtual Product
			true, // Display Opt-In checkbox on Checkout
			true, // Check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was now added to ConvertKit.
		$I->apiCheckSubscriberExists($I, $result['email_address']);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Customer is NOT subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - The opt in checkbox is unchecked on the WooCommerce checkout, and
	 * - The Customer purchases a 'virtual' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenUncheckedWithFormAndVirtualProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'virtual', // Virtual Product
			true, // Display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was not added to ConvertKit.
		$I->apiCheckSubscriberDoesNotExist($I, $result['email_address']);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is disabled in the integration Settings, and
	 * - The Customer purchases a 'virtual' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInDisabledWithFormAndVirtualProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'virtual', // Virtual Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was added to ConvertKit.
		$I->apiCheckSubscriberExists($I, $result['email_address']);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Customer is not subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - No Form is selected in the integration Settings, and
	 * - The opt in checkbox is checked on the WooCommerce checkout, and
	 * - The Customer purchases a 'virtual' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is marked as processing.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenCheckedWithNoFormAndVirtualProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'virtual', // Virtual Product
			true, // Display Opt-In checkbox on Checkout
			true, // Check Opt-In checkbox on Checkout
			'Select a subscription option...', // Don't select a Form to subscribe the email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was still not added to ConvertKit.
		$I->apiCheckSubscriberDoesNotExist($I, $result['email_address']);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Customer is NOT subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - No Form is selected in the integration Settings, and
	 * - The opt in checkbox is unchecked on the WooCommerce checkout, and
	 * - The Customer purchases a 'virtual' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenUncheckedWithNoFormAndVirtualProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'virtual', // Virtual Product
			true, // Display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			'Select a subscription option...', // Don't select a Form to subscribe the email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was still not added to ConvertKit.
		$I->apiCheckSubscriberDoesNotExist($I, $result['email_address']);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is disabled in the integration Settings, and
	 * - No Form is selected in the integration Settings, and
	 * - The Customer purchases a 'virtual' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInDisabledWithNoFormAndVirtualProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'virtual', // Virtual Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			'Select a subscription option...', // Don't select a Form to subscribe the email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the email address was still not added to ConvertKit.
		$I->apiCheckSubscriberDoesNotExist($I, $result['email_address']);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}
	
}