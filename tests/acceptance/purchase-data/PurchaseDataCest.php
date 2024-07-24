<?php
/**
 * Tests that Purchase Data does (or does not) get sent to ConvertKit based on the integration
 * settings.
 *
 * @since   1.4.2
 */
class PurchaseDataCest
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
		// Activate Plugin.
		$I->activateWooCommerceAndConvertKitPlugins($I);

		// Enable HPOS.
		$I->setupWooCommerceHPOS($I);

		// Activate Custom Order Numbers, so that we can prefix Order IDs with
		// an environment-specific string.
		$I->activateThirdPartyPlugin($I, 'custom-order-numbers-for-woocommerce');

		// Setup WooCommerce Plugin.
		$I->setupWooCommercePlugin($I);

		// Setup Custom Order Numbers Plugin.
		$I->setupCustomOrderNumbersPlugin($I);

		// Populate resoruces.
		$I->setupConvertKitPluginResources($I);
	}

	/**
	 * Test that the Customer's purchase is sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Order is created via the frontend checkout.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSendPurchaseDataWithSimpleProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			[
				'send_purchase_data' => true,
			]
		);

		// Confirm that the purchase was added to ConvertKit.
		$purchaseDataID = $I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully: ID [' . $id . ']');

		// Confirm that the Transaction ID is stored in the Order's metadata.
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_sent', 'yes', true);
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_id', $purchaseDataID, true);
	}

	/**
	 * Test that the Customer's purchase is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Order is created via the frontend checkout.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testDontSendPurchaseDataWithSimpleProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig($I);

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
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSendPurchaseDataWithVirtualProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			[
				'product_type'       => 'virtual',
				'send_purchase_data' => true,
			]
		);

		// Confirm that the purchase was added to ConvertKit.
		$purchaseDataID = $I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully: ID [' . $id . ']');

		// Confirm that the Transaction ID is stored in the Order's metadata.
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_sent', 'yes', true);
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_id', $purchaseDataID, true);
	}

	/**
	 * Test that the Customer's purchase is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Customer purchases a 'Virtual' WooCommerce Product, and
	 * - The Order is created via the frontend checkout.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testDontSendPurchaseDataWithVirtualProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			[
				'product_type' => 'virtual',
			]
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
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSendPurchaseDataWithZeroValueProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			[
				'product_type'       => 'zero',
				'send_purchase_data' => true,
			]
		);

		// Confirm that the purchase was added to ConvertKit.
		$purchaseDataID = $I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully: ID [' . $id . ']');

		// Confirm that the Transaction ID is stored in the Order's metadata.
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_sent', 'yes', true);
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_id', $purchaseDataID, true);
	}

	/**
	 * Test that the Customer's purchase is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Customer purchases a WooCommerce Product with zero value, and
	 * - The Order is created via the frontend checkout.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testDontSendPurchaseDataWithZeroValueProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			[
				'product_type' => 'zero',
			]
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
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSendPurchaseDataWithSimpleProductNoPaymentMethodManualOrder(AcceptanceTester $I)
	{
		// Enable Integration and define its Access and Refresh Tokens.
		$I->setupConvertKitPlugin(
			$I,
			$_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
			$_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			false, // Don't define a subscribe event.
			false, // Don't subscribe the customer to anything.
			'first', // Name format.
			false, // Don't map custom fields.
			false, // Don't display an opt in.
			'processing' // Send purchase data on 'processing' event.
		);

		// Create Product for this test.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID.
			'Simple Product', // Product Name.
			'wc-processing', // Order Status.
			'' // Payment Method.
		);

		// Confirm that the purchase was added to ConvertKit.
		$purchaseDataID = $I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully: ID [' . $id . ']');

		// Confirm that the Transaction ID is stored in the Order's metadata.
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_sent', 'yes', true);
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_id', $purchaseDataID, true);
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Order contains a 'Simple' WooCommerce Product, and
	 * - The Order's payment method is blank (N/A), and
	 * - The Order is created via the WordPress Administration interface.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testDontSendPurchaseDataWithSimpleProductNoPaymentMethodManualOrder(AcceptanceTester $I)
	{
		// Enable Integration and define its Access and Refresh Tokens.
		$I->setupConvertKitPlugin(
			$I,
			$_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
			$_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			false, // Don't define a subscribe event.
			false, // Don't subscribe the customer to anything.
			'first', // Name format.
			false, // Don't map custom fields.
			false, // Don't display an opt in.
			false // Don't send purchase data.
		);

		// Create Product for this test.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID.
			'Simple Product', // Product Name.
			'wc-processing', // Order Status.
			'' // Payment Method.
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
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSendPurchaseDataWithVirtualProductNoPaymentMethodManualOrder(AcceptanceTester $I)
	{
		// Enable Integration and define its Access and Refresh Tokens.
		$I->setupConvertKitPlugin(
			$I,
			$_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
			$_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			false, // Don't define a subscribe event.
			false, // Don't subscribe the customer to anything.
			'first', // Name format.
			false, // Don't map custom fields.
			false, // Don't display an opt in.
			'processing' // Send purchase data on 'processing' event.
		);

		// Create Product for this test.
		$productID = $I->wooCommerceCreateVirtualProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID.
			'Virtual Product', // Product Name.
			'wc-processing', // Order Status.
			'' // Payment Method.
		);

		// Confirm that the purchase was added to ConvertKit.
		$purchaseDataID = $I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully: ID [' . $id . ']');

		// Confirm that the Transaction ID is stored in the Order's metadata.
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_sent', 'yes', true);
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_id', $purchaseDataID, true);
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Order contains a 'Virtual' WooCommerce Product, and
	 * - The Order's payment method is blank (N/A), and
	 * - The Order is created via the WordPress Administration interface.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testDontSendPurchaseDataWithVirtualProductNoPaymentMethodManualOrder(AcceptanceTester $I)
	{
		// Enable Integration and define its Access and Refresh Tokens.
		$I->setupConvertKitPlugin(
			$I,
			$_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
			$_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			false, // Don't define a subscribe event.
			false, // Don't subscribe the customer to anything.
			'first', // Name format.
			false, // Don't map custom fields.
			false, // Don't display an opt in.
			false // Don't send purchase data.
		);

		// Create Product for this test.
		$productID = $I->wooCommerceCreateVirtualProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID.
			'Virtual Product', // Product Name.
			'wc-processing', // Order Status.
			'' // Payment Method.
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
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSendPurchaseDataWithZeroValueProductNoPaymentMethodManualOrder(AcceptanceTester $I)
	{
		// Enable Integration and define its Access and Refresh Tokens.
		$I->setupConvertKitPlugin(
			$I,
			$_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
			$_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			false, // Don't define a subscribe event.
			false, // Don't subscribe the customer to anything.
			'first', // Name format.
			false, // Don't map custom fields.
			false, // Don't display an opt in.
			'processing' // Send purchase data on 'processing' event.
		);

		// Create Product for this test.
		$productID = $I->wooCommerceCreateZeroValueProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID.
			'Zero Value Product', // Product Name.
			'wc-processing', // Order Status.
			'' // Payment Method.
		);

		// Confirm that the purchase was added to ConvertKit.
		$purchaseDataID = $I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully: ID [' . $id . ']');

		// Confirm that the Transaction ID is stored in the Order's metadata.
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_sent', 'yes', true);
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_id', $purchaseDataID, true);
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Order contains a 'Zero Value' WooCommerce Product, and
	 * - The Order's payment method is blank (N/A), and
	 * - The Order is created via the WordPress Administration interface.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testDontSendPurchaseDataWithZeroValueProductNoPaymentMethodManualOrder(AcceptanceTester $I)
	{
		// Enable Integration and define its Access and Refresh Tokens.
		$I->setupConvertKitPlugin(
			$I,
			$_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
			$_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			false, // Don't define a subscribe event.
			false, // Don't subscribe the customer to anything.
			'first', // Name format.
			false, // Don't map custom fields.
			false, // Don't display an opt in.
			false // Don't send purchase data.
		);

		// Create Product for this test.
		$productID = $I->wooCommerceCreateZeroValueProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID.
			'Zero Value Product', // Product Name.
			'wc-processing', // Order Status.
			'' // Payment Method.
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
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSendPurchaseDataWithSimpleProductCODManualOrder(AcceptanceTester $I)
	{
		// Enable Integration and define its Access and Refresh Tokens.
		$I->setupConvertKitPlugin(
			$I,
			$_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
			$_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			false, // Don't define a subscribe event.
			false, // Don't subscribe the customer to anything.
			'first', // Name format.
			false, // Don't map custom fields.
			false, // Don't display an opt in.
			'processing' // Send purchase data on 'processing' event.
		);

		// Create Product for this test.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID.
			'Simple Product', // Product Name.
			'wc-processing', // Order Status.
			'cod' // Payment Method.
		);

		// Confirm that the purchase was added to ConvertKit.
		$purchaseDataID = $I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully: ID [' . $id . ']');

		// Confirm that the Transaction ID is stored in the Order's metadata.
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_sent', 'yes', true);
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_id', $purchaseDataID, true);
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Order contains a 'Simple' WooCommerce Product, and
	 * - The Order's payment method is Cash on Delivery (COD), and
	 * - The Order is created via the WordPress Administration interface.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testDontSendPurchaseDataWithSimpleProductCODManualOrder(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig($I);

		// Create Product for this test.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID.
			'Simple Product', // Product Name.
			'wc-processing', // Order Status.
			'cod' // Payment Method.
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
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSendPurchaseDataWithVirtualProductCODManualOrder(AcceptanceTester $I)
	{
		// Enable Integration and define its Access and Refresh Tokens.
		$I->setupConvertKitPlugin(
			$I,
			$_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
			$_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			false, // Don't define a subscribe event.
			false, // Don't subscribe the customer to anything.
			'first', // Name format.
			false, // Don't map custom fields.
			false, // Don't display an opt in.
			'processing' // Send purchase data on 'processing' event.
		);

		// Create Product for this test.
		$productID = $I->wooCommerceCreateVirtualProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID.
			'Virtual Product', // Product Name.
			'wc-processing', // Order Status.
			'cod' // Payment Method.
		);

		// Confirm that the purchase was added to ConvertKit.
		$purchaseDataID = $I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully: ID [' . $id . ']');

		// Confirm that the Transaction ID is stored in the Order's metadata.
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_sent', 'yes', true);
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_id', $purchaseDataID, true);
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Order contains a 'Virtual' WooCommerce Product, and
	 * - The Order's payment method is Cash on Delivery (COD), and
	 * - The Order is created via the WordPress Administration interface.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testDontSendPurchaseDataWithVirtualProductCODManualOrder(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig($I);

		// Create Product for this test.
		$productID = $I->wooCommerceCreateVirtualProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID.
			'Virtual Product', // Product Name.
			'wc-processing', // Order Status.
			'cod' // Payment Method.
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
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSendPurchaseDataWithZeroValueProductCODManualOrder(AcceptanceTester $I)
	{
		// Enable Integration and define its Access and Refresh Tokens.
		$I->setupConvertKitPlugin(
			$I,
			$_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
			$_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			false, // Don't define a subscribe event.
			false, // Don't subscribe the customer to anything.
			'first', // Name format.
			false, // Don't map custom fields.
			false, // Don't display an opt in.
			'processing' // Send purchase data on 'processing' event.
		);

		// Create Product for this test.
		$productID = $I->wooCommerceCreateZeroValueProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID.
			'Zero Value Product', // Product Name.
			'wc-processing', // Order Status.
			'cod' // Payment Method.
		);

		// Confirm that the purchase was added to ConvertKit.
		$purchaseDataID = $I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully: ID [' . $id . ']');

		// Confirm that the Transaction ID is stored in the Order's metadata.
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_sent', 'yes', true);
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_id', $purchaseDataID, true);
	}

	/**
	 * Test that a manual order (an order created in the WordPress Administration interface) is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is disabled in the integration Settings, and
	 * - The Order contains a 'Zero Value' WooCommerce Product, and
	 * - The Order's payment method is Cash on Delivery (COD), and
	 * - The Order is created via the WordPress Administration interface.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testDontSendPurchaseDataWithZeroValueProductCODManualOrder(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig($I);

		// Create Product for this test.
		$productID = $I->wooCommerceCreateZeroValueProduct($I);

		// Create Manual Order.
		$result = $I->wooCommerceCreateManualOrder(
			$I,
			$productID, // Product ID.
			'Zero Value Product', // Product Name.
			'wc-processing', // Order Status.
			'cod' // Payment Method.
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
	 * - The Order is created via the frontend checkout, and
	 * - The Order's status is changed from processing to completed.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSendPurchaseDataOnOrderCompletedWithSimpleProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			[
				'send_purchase_data' => 'completed',
			]
		);

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);

		// Check that the Order's Notes does not include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');

		// Change Order Status = Completed.
		$I->wooCommerceChangeOrderStatus($I, $result['order_id'], 'wc-completed');

		// Confirm that the purchase was added to ConvertKit.
		$purchaseDataID = $I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully: ID [' . $id . ']');

		// Confirm that the Transaction ID is stored in the Order's metadata.
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_sent', 'yes', true);
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_id', $purchaseDataID, true);
	}

	/**
	 * Test that the Customer's purchase is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Send Purchase Data Event is set to Order Completed, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Order is created via the frontend checkout, and
	 * - The Order's status is changed from processing to cancelled.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testDontSendPurchaseDataOnOrderCancelledWithSimpleProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			[
				'send_purchase_data' => 'completed',
			]
		);

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);

		// Check that the Order's Notes does not include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');

		// Change Order Status = Completed.
		$I->wooCommerceChangeOrderStatus($I, $result['order_id'], 'wc-cancelled');

		// Confirm that the purchase was not added to ConvertKit.
		$I->apiCheckPurchaseDoesNotExist($I, $result['order_id'], $result['email_address']);

		// Check that the Order's Notes does not include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');
	}

	/**
	 * Test that the name format setting is honored for the created subscriber in ConvertKit when
	 * they opt in to subscribe and purchase data is also sent.
	 *
	 * @since   1.6.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSendPurchaseDataNameFormatHonoredWhenSubscribed(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			[
				'plugin_form_tag_sequence' => 'form:' . $_ENV['CONVERTKIT_API_FORM_ID'],
				'subscription_event'       => 'pending',
				'send_purchase_data'       => true,
				'name_format'              => 'both',
			]
		);

		// Confirm that the email address was now added to ConvertKit with a valid name.
		$subscriber = $I->apiCheckSubscriberExists($I, $result['email_address'], 'First Last');

		// Confirm that the purchase was added to ConvertKit.
		$purchaseDataID = $I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully: ID [' . $id . ']');

		// Confirm that the Transaction ID is stored in the Order's metadata.
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_sent', 'yes', true);
		$I->wooCommerceOrderMetaKeyAndValueExist($I, $result['order_id'], 'ckwc_purchase_data_id', $purchaseDataID, true);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($subscriber['id']);
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
		$I->deactivateThirdPartyPlugin($I, 'custom-order-numbers-for-woocommerce');
		$I->resetConvertKitPlugin($I);
	}
}
