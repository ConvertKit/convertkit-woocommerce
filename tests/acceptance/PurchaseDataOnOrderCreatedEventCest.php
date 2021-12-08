<?php
/**
 * Tests that Purchase Data does (or does not) get sent to ConvertKit based on the integration
 * settings when the Subscribe Event = Order Created, and an order is placed through WooCommerce
 * 
 * @since 	1.9.6
 */
class PurchaseDataOnOrderCreatedEventCest
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
	 * Test that the Customer's purchase is sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Order is submitted as a purchase to ConvertKit once checkout is completed.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataWithSimpleProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to (not used)
			'Order Created', // Perform on WooCommerce "Order Created" event (not used)
			true // Send purchase data to ConvertKit
		);
	
		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address']);
	}

	/**
	 * Test that the Customer's purchase is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Order is not submitted as a purchase to ConvertKit once checkout is completed.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testDontSendPurchaseDataWithSimpleProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to (not used)
			'Order Created', // Perform on WooCommerce "Order Created" event (not used)
			false // Send purchase data to ConvertKit
		);

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);
	}

	/**
	 * Test that the Customer's purchase is sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Customer purchases a 'Virtual' WooCommerce Product, and
	 * - The Order is submitted as a purchase to ConvertKit once checkout is completed.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataWithVirtualProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'virtual', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to (not used)
			'Order Created', // Perform on WooCommerce "Order Created" event (not used)
			true // Send purchase data to ConvertKit
		);
	
		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address']);
	}

	/**
	 * Test that the Customer's purchase is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Customer purchases a 'Virtual' WooCommerce Product, and
	 * - The Order is not submitted as a purchase to ConvertKit once checkout is completed.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testDontSendPurchaseDataWithVirtualProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'virtual', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to (not used)
			'Order Created', // Perform on WooCommerce "Order Created" event (not used)
			false // Send purchase data to ConvertKit
		);

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);
	}
}