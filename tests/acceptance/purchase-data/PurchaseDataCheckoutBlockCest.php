<?php
/**
 * Tests that Purchase Data does (or does not) get sent to ConvertKit based on the integration
 * settings.
 *
 * @since   1.7.1
 */
class PurchaseDataCheckoutBlockCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.7.1
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
	 * @since   1.7.1
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
				'use_legacy_checkout' => false,
			]
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
	 * @since   1.7.1
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testDontSendPurchaseDataWithSimpleProductCheckout(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			[
				'use_legacy_checkout' => false,
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
	 * - The Customer purchases a 'Virtual' WooCommerce Product, and
	 * - The Order is created via the frontend checkout.
	 *
	 * @since   1.7.1
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
				'use_legacy_checkout' => false,
			]
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
	 * @since   1.7.1
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
				'use_legacy_checkout' => false,
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
	 * @since   1.7.1
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
				'use_legacy_checkout' => false,
			]
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
	 * @since   1.7.1
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
				'use_legacy_checkout' => false,
			]
		);

		// Confirm that the purchase was added to ConvertKit.
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
	 * @since   1.7.1
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
				'use_legacy_checkout' => false,
			]
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
	 * Test that the Customer's purchase is not sent to ConvertKit when:
	 * - The 'Send purchase data to ConvertKit' is enabled in the integration Settings, and
	 * - The Send Purchase Data Event is set to Order Completed, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Order is created via the frontend checkout, and
	 * - The Order's status is changed from processing to cancelled.
	 *
	 * @since   1.7.1
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
				'use_legacy_checkout' => false,
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
				'use_legacy_checkout' => false,
			]
		);

		// Confirm that the email address was now added to ConvertKit.
		$I->apiCheckSubscriberExists($I, $result['email_address']);

		// Confirm that the subscriber's name = First Last.
		$I->apiCheckSubscriberEmailAndNameExists($I, $result['email_address'], 'First Last');

		// Confirm that the purchase was added to ConvertKit.
		$I->apiCheckPurchaseExists($I, $result['order_id'], $result['email_address'], $result['product_id']);

		// Check that the Order's Notes include a note from the Plugin confirming the purchase was added to ConvertKit.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], '[ConvertKit] Purchase Data sent successfully');

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
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateWooCommerceAndConvertKitPlugins($I);
		$I->deactivateThirdPartyPlugin($I, 'custom-order-numbers-for-woocommerce');
		$I->resetConvertKitPlugin($I);
	}
}
