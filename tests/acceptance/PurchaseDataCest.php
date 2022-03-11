<?php
/**
 * Tests that Purchase Data does (or does not) get sent to ConvertKit based on the integration
 * settings.
 * 
 * @since 	1.4.2
 */
class PurchaseDataCest
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

		// Enable Integration and define its API Keys.
		$I->setupConvertKitPlugin($I);
	}

	/**
	 * Test that the Customer's purchase is sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Order is created via the frontend checkout.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataWithSimpleProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			false, // Form to subscribe email address to (not used)
			false, // Subscribe Event
			true // Send purchase data to ConvertKit
		);

		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that the Customer's purchase is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Order is created via the frontend checkout.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testDontSendPurchaseDataWithSimpleProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			false, // Form to subscribe email address to (not used)
			false, // Subscribe Event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);

		// Check that the Order's Notes does not include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that the Customer's purchase is sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Customer purchases a 'Virtual' WooCommerce Product, and
	 * - The Order is created via the frontend checkout.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataWithVirtualProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'virtual', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			false, // Form to subscribe email address to (not used)
			false, // Subscribe Event
			true // Send purchase data to ConvertKit
		);

		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that the Customer's purchase is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Customer purchases a 'Virtual' WooCommerce Product, and
	 * - The Order is created via the frontend checkout.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testDontSendPurchaseDataWithVirtualProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'virtual', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			false, // Form to subscribe email address to (not used)
			false, // Subscribe Event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);

		// Check that the Order's Notes does not include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that the Customer's purchase is sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Customer purchases a WooCommerce Product with zero value, and
	 * - The Order is created via the frontend checkout.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataWithZeroValueProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'zero', // Zero value Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			false, // Form to subscribe email address to (not used)
			false, // Subscribe Event
			true // Send purchase data to ConvertKit
		);

		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that the Customer's purchase is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Customer purchases a WooCommerce Product with zero value, and
	 * - The Order is created via the frontend checkout.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testDontSendPurchaseDataWithZeroValueProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'zero', // Zero value Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			false, // Form to subscribe email address to (not used)
			false, // Subscribe Event
			false // Don't send purchase data to ConvertKit
		);

		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);

		// Check that the Order's Notes does not include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Order contains a 'Simple' WooCommerce Product, and
	 * - The Order's payment method is blank (N/A), and
	 * - The Order is created via the WordPress Administration interface.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataWithSimpleProductNoPaymentMethodManualOrder(AcceptanceTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsScreen($I);

		// Enable Sending Purchase Data.
		$I->checkOption('#woocommerce_ckwc_send_purchases');	

		// Save.
		$I->click('Save changes');

		// Create Product for this test.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID
			'Simple Product', // Product Name
			'wc-processing', // Order Status
			'' // Payment Method
		);

		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Order contains a 'Simple' WooCommerce Product, and
	 * - The Order's payment method is blank (N/A), and
	 * - The Order is created via the WordPress Administration interface.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testDontSendPurchaseDataWithSimpleProductNoPaymentMethodManualOrder(AcceptanceTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsScreen($I);

		// Disable Sending Purchase Data.
		$I->uncheckOption('#woocommerce_ckwc_send_purchases');	

		// Save.
		$I->click('Save changes');

		// Create Product for this test.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID
			'Simple Product', // Product Name
			'wc-processing', // Order Status
			'' // Payment Method
		);

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);

		// Check that the Order's Notes does not include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Order contains a 'Virtual' WooCommerce Product, and
	 * - The Order's payment method is blank (N/A), and
	 * - The Order is created via the WordPress Administration interface.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataWithVirtualProductNoPaymentMethodManualOrder(AcceptanceTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsScreen($I);

		// Enable Sending Purchase Data.
		$I->checkOption('#woocommerce_ckwc_send_purchases');	

		// Save.
		$I->click('Save changes');

		// Create Product for this test.
		$productID = $I->wooCommerceCreateVirtualProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID
			'Virtual Product', // Product Name
			'wc-processing', // Order Status
			'' // Payment Method
		);

		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Order contains a 'Virtual' WooCommerce Product, and
	 * - The Order's payment method is blank (N/A), and
	 * - The Order is created via the WordPress Administration interface.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testDontSendPurchaseDataWithVirtualProductNoPaymentMethodManualOrder(AcceptanceTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsScreen($I);

		// Disable Sending Purchase Data.
		$I->uncheckOption('#woocommerce_ckwc_send_purchases');	

		// Save.
		$I->click('Save changes');

		// Create Product for this test.
		$productID = $I->wooCommerceCreateVirtualProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID
			'Virtual Product', // Product Name
			'wc-processing', // Order Status
			'' // Payment Method
		);

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);

		// Check that the Order's Notes does not include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Order contains a 'Zero Value' WooCommerce Product, and
	 * - The Order's payment method is blank (N/A), and
	 * - The Order is created via the WordPress Administration interface.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataWithZeroValueProductNoPaymentMethodManualOrder(AcceptanceTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsScreen($I);

		// Enable Sending Purchase Data.
		$I->checkOption('#woocommerce_ckwc_send_purchases');	

		// Save.
		$I->click('Save changes');

		// Create Product for this test.
		$productID = $I->wooCommerceCreateZeroValueProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID
			'Zero Value Product', // Product Name
			'wc-processing', // Order Status
			'' // Payment Method
		);

		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Order contains a 'Zero Value' WooCommerce Product, and
	 * - The Order's payment method is blank (N/A), and
	 * - The Order is created via the WordPress Administration interface.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testDontSendPurchaseDataWithZeroValueProductNoPaymentMethodManualOrder(AcceptanceTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsScreen($I);

		// Disable Sending Purchase Data.
		$I->uncheckOption('#woocommerce_ckwc_send_purchases');	

		// Save.
		$I->click('Save changes');

		// Create Product for this test.
		$productID = $I->wooCommerceCreateZeroValueProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID
			'Zero Value Product', // Product Name
			'wc-processing', // Order Status
			'' // Payment Method
		);

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);

		// Check that the Order's Notes does not include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Order contains a 'Simple' WooCommerce Product, and
	 * - The Order's payment method is Cash on Delivery (COD), and
	 * - The Order is created via the WordPress Administration interface.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataWithSimpleProductCODManualOrder(AcceptanceTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsScreen($I);

		// Enable Sending Purchase Data.
		$I->checkOption('#woocommerce_ckwc_send_purchases');	

		// Save.
		$I->click('Save changes');

		// Create Product for this test.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID
			'Simple Product', // Product Name
			'wc-processing', // Order Status
			'cod' // Payment Method
		);

		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Order contains a 'Simple' WooCommerce Product, and
	 * - The Order's payment method is Cash on Delivery (COD), and
	 * - The Order is created via the WordPress Administration interface.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testDontSendPurchaseDataWithSimpleProductCODManualOrder(AcceptanceTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsScreen($I);

		// Disable Sending Purchase Data.
		$I->uncheckOption('#woocommerce_ckwc_send_purchases');	

		// Save.
		$I->click('Save changes');

		// Create Product for this test.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID
			'Simple Product', // Product Name
			'wc-processing', // Order Status
			'cod' // Payment Method
		);

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);

		// Check that the Order's Notes does not include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Order contains a 'Virtual' WooCommerce Product, and
	 * - The Order's payment method is Cash on Delivery (COD), and
	 * - The Order is created via the WordPress Administration interface.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataWithVirtualProductCODManualOrder(AcceptanceTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsScreen($I);

		// Enable Sending Purchase Data.
		$I->checkOption('#woocommerce_ckwc_send_purchases');	

		// Save.
		$I->click('Save changes');

		// Create Product for this test.
		$productID = $I->wooCommerceCreateVirtualProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID
			'Virtual Product', // Product Name
			'wc-processing', // Order Status
			'cod' // Payment Method
		);

		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Order contains a 'Virtual' WooCommerce Product, and
	 * - The Order's payment method is Cash on Delivery (COD), and
	 * - The Order is created via the WordPress Administration interface.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testDontSendPurchaseDataWithVirtualProductCODManualOrder(AcceptanceTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsScreen($I);

		// Disable Sending Purchase Data.
		$I->uncheckOption('#woocommerce_ckwc_send_purchases');	

		// Save.
		$I->click('Save changes');

		// Create Product for this test.
		$productID = $I->wooCommerceCreateVirtualProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID
			'Virtual Product', // Product Name
			'wc-processing', // Order Status
			'cod' // Payment Method
		);

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);

		// Check that the Order's Notes does not include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Order contains a 'Zero Value' WooCommerce Product, and
	 * - The Order's payment method is Cash on Delivery (COD), and
	 * - The Order is created via the WordPress Administration interface.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataWithZeroValueProductCODManualOrder(AcceptanceTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsScreen($I);

		// Enable Sending Purchase Data.
		$I->checkOption('#woocommerce_ckwc_send_purchases');	

		// Save.
		$I->click('Save changes');

		// Create Product for this test.
		$productID = $I->wooCommerceCreateZeroValueProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID
			'Zero Value Product', // Product Name
			'wc-processing', // Order Status
			'cod' // Payment Method
		);

		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Order contains a 'Zero Value' WooCommerce Product, and
	 * - The Order's payment method is Cash on Delivery (COD), and
	 * - The Order is created via the WordPress Administration interface.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testDontSendPurchaseDataWithZeroValueProductCODManualOrder(AcceptanceTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadConvertKitSettingsScreen($I);

		// Disable Sending Purchase Data.
		$I->uncheckOption('#woocommerce_ckwc_send_purchases');	

		// Save.
		$I->click('Save changes');

		// Create Product for this test.
		$productID = $I->wooCommerceCreateZeroValueProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID
			'Zero Value Product', // Product Name
			'wc-processing', // Order Status
			'cod' // Payment Method
		);

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);

		// Check that the Order's Notes does not include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that the Customer's purchase is sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Send Purchase Data Event is set to Order Completed, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Order is created via the frontend checkout.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testSendPurchaseDataOnOrderCompletedWithSimpleProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			false, // Don't display Opt-In checkbox on Checkout
			false, // Don't check Opt-In checkbox on Checkout
			false, // Form to subscribe email address to (not used)
			false, // Subscribe Event
			'Order Completed' // Send purchase data to ConvertKit when the Order status = Order Completed
		);

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);

		// Check that the Order's Notes does not include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');

		// Change Order Status = Completed.
		$I->wooCommerceChangeOrderStatus($I, $result['order_id'], 'wc-completed');

		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 * 
	 * @since 	1.4.4
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateConvertKitPlugin($I);
		$I->resetConvertKitPlugin($I);
	}
}