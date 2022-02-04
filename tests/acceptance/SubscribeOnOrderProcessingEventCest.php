<?php
/**
 * Tests the various settings do (or do not) subscribe the customer to a ConvertKit Form,
 * Tag or Sequence on the Order Processing event when an order is placed through WooCommerce,
 * and that any Order data is correctly stored e.g. Order Notes.
 * 
 * @since 	1.4.2
 */
class SubscribeOnOrderProcessingEventCest
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
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - The opt in checkbox is checked on the WooCommerce checkout, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is marked as processing.
	 * 
	 * @since 	1.4.2
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
		$subscriber = $I->apiCheckSubscriberExists($I, $result['email_address']);

		// Confirm the subscriber's custom field data is empty, as no Order to Custom Field mapping was specified
		// in the integration's settings.
		$I->apiCustomFieldDataIsEmpty($I, $subscriber);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);

		// Check that the Order's Notes include a note from the Plugin confirming the Customer was subscribed to the Form.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], 'Customer subscribed to the Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ' [' . $_ENV['CONVERTKIT_API_FORM_ID'] . ']');
	}

	/**
	 * Test that the Customer is NOT subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - The opt in checkbox is unchecked on the WooCommerce checkout, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.4.2
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
	
		// Check that the Order's Notes does include a note from the Plugin confirming the Customer was subscribed.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], 'Customer subscribed');
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is disabled in the integration Settings, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.4.2
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
		$subscriber = $I->apiCheckSubscriberExists($I, $result['email_address']);

		// Confirm the subscriber's custom field data is empty, as no Order to Custom Field mapping was specified
		// in the integration's settings.
		$I->apiCustomFieldDataIsEmpty($I, $subscriber);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
		
		// Check that the Order's Notes include a note from the Plugin confirming the Customer was subscribed to the Form.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], 'Customer subscribed to the Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ' [' . $_ENV['CONVERTKIT_API_FORM_ID'] . ']');
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - Order data is mapped to ConvertKit Custom fields in the integration Settings, and
	 * - The opt in checkbox is checked on the WooCommerce checkout, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is marked as processing.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenCheckedWithFormCustomFieldsAndSimpleProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			true, // Display Opt-In checkbox on Checkout
			true, // Check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false, // Don't send purchase data to ConvertKit
			false, // Don't define a Product level Form, Tag or Sequence
			true // Map Order data to Custom Fields
		);

		// Confirm that the email address was now added to ConvertKit.
		$subscriber = $I->apiCheckSubscriberExists($I, $result['email_address']);

		// Confirm the subscriber's custom field data exists and is correct.
		$I->apiCustomFieldDataIsValid($I, $subscriber);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - Order data is mapped to ConvertKit Custom fields in the integration Settings, and
	 * - The opt in checkbox is checked on the WooCommerce checkout, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is marked as processing.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenCheckedWithTagCustomFieldsAndSimpleProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			true, // Display Opt-In checkbox on Checkout
			true, // Check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_TAG_NAME'], // Tag to subscribe email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false, // Don't send purchase data to ConvertKit
			false, // Don't define a Product level Form, Tag or Sequence
			true // Map Order data to Custom Fields
		);

		// Confirm that the email address was now added to ConvertKit.
		$subscriber = $I->apiCheckSubscriberExists($I, $result['email_address']);

		// Confirm the subscriber's custom field data exists and is correct.
		$I->apiCustomFieldDataIsValid($I, $subscriber);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - Order data is mapped to ConvertKit Custom fields in the integration Settings, and
	 * - The opt in checkbox is checked on the WooCommerce checkout, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is marked as processing.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenCheckedWithSequenceCustomFieldsAndSimpleProduct(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			true, // Display Opt-In checkbox on Checkout
			true, // Check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_SEQUENCE_NAME'], // Tag to subscribe email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false, // Don't send purchase data to ConvertKit
			false, // Don't define a Product level Form, Tag or Sequence
			true // Map Order data to Custom Fields
		);

		// Confirm that the email address was now added to ConvertKit.
		$subscriber = $I->apiCheckSubscriberExists($I, $result['email_address']);

		// Confirm the subscriber's custom field data exists and is correct.
		$I->apiCustomFieldDataIsValid($I, $subscriber);

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
	 * @since 	1.4.2
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

		// Check that the Order's Notes does include a note from the Plugin confirming the Customer was subscribed.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], 'Customer subscribed');
	}

	/**
	 * Test that the Customer is NOT subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - No Form is selected in the integration Settings, and
	 * - The opt in checkbox is unchecked on the WooCommerce checkout, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.4.2
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

		// Check that the Order's Notes does include a note from the Plugin confirming the Customer was subscribed.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], 'Customer subscribed');
	}

	/**
	 * Test that the Customer is not subscribed to ConvertKit when:
	 * - The opt in checkbox is disabled in the integration Settings, and
	 * - No Form is selected in the integration Settings, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.4.2
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

		// Check that the Order's Notes does include a note from the Plugin confirming the Customer was subscribed.
		$I->wooCommerceOrderNoteDoesNotExist($I, $result['order_id'], 'Customer subscribed');
	
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - The opt in checkbox is checked on the WooCommerce checkout, and
	 * - The 'Simple' WooCommerce Product also defines a Form (separate to the Plugin settings), and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is marked as processing.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenCheckedWithFormAndSimpleProductWithProductForm(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			true, // Display Opt-In checkbox on Checkout
			true, // Check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false, // Don't send purchase data to ConvertKit
			'form:'.$_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] // Product level Form to subscribe email address to
		);

		// Confirm that the email address was now added to ConvertKit.
		$subscriber = $I->apiCheckSubscriberExists($I, $result['email_address']);

		// Confirm the subscriber's custom field data is empty, as no Order to Custom Field mapping was specified
		// in the integration's settings.
		$I->apiCustomFieldDataIsEmpty($I, $subscriber);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);

		// Check that the Order's Notes include a note from the Plugin confirming the Customer was subscribed to the Form.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], 'Customer subscribed to the Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ' [' . $_ENV['CONVERTKIT_API_FORM_ID'] . ']');
	
		// Check that the Order's Notes include a note from the Plugin confirming the Customer was subscribed to the Legacy Form.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], 'Customer subscribed to the Form: ' . $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'] . ' [' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . ']');
	}
	
	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - The opt in checkbox is checked on the WooCommerce checkout, and
	 * - The 'Simple' WooCommerce Product also defines a Tag (separate to the Plugin settings), and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is marked as processing.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenCheckedWithFormAndSimpleProductWithProductTag(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			true, // Display Opt-In checkbox on Checkout
			true, // Check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false, // Don't send purchase data to ConvertKit
			'tag:'.$_ENV['CONVERTKIT_API_TAG_ID'] // Product level Tag to subscribe email address to
		);

		// Confirm that the email address was now added to ConvertKit.
		$subscriber = $I->apiCheckSubscriberExists($I, $result['email_address']);

		// Confirm the subscriber's custom field data is empty, as no Order to Custom Field mapping was specified
		// in the integration's settings.
		$I->apiCustomFieldDataIsEmpty($I, $subscriber);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);

		// Check that the Order's Notes include a note from the Plugin confirming the Customer was subscribed to the Form.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], 'Customer subscribed to the Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ' [' . $_ENV['CONVERTKIT_API_FORM_ID'] . ']');
	
		// Check that the Order's Notes include a note from the Plugin confirming the Customer was subscribed to the Tag.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], 'Customer subscribed to the Tag: ' . $_ENV['CONVERTKIT_API_TAG_NAME'] . ' [' . $_ENV['CONVERTKIT_API_TAG_ID'] . ']');
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - The opt in checkbox is checked on the WooCommerce checkout, and
	 * - The 'Simple' WooCommerce Product also defines a Sequence (separate to the Plugin settings), and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is marked as processing.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenCheckedWithFormAndSimpleProductWithProductSequence(AcceptanceTester $I)
	{
		// Create Product and Checkout for this test.
		$result = $I->wooCommerceCreateProductAndCheckoutWithConfig(
			$I,
			'simple', // Simple Product
			true, // Display Opt-In checkbox on Checkout
			true, // Check Opt-In checkbox on Checkout
			$_ENV['CONVERTKIT_API_FORM_NAME'], // Form to subscribe email address to
			'Order Processing', // Subscribe on WooCommerce "Order Processing" event
			false, // Don't send purchase data to ConvertKit
			'course:'.$_ENV['CONVERTKIT_API_SEQUENCE_ID'] // Product level Sequence to subscribe email address to
		);

		// Confirm that the email address was now added to ConvertKit.
		$subscriber = $I->apiCheckSubscriberExists($I, $result['email_address']);

		// Confirm the subscriber's custom field data is empty, as no Order to Custom Field mapping was specified
		// in the integration's settings.
		$I->apiCustomFieldDataIsEmpty($I, $subscriber);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);
		
		// Check that the Order's Notes include a note from the Plugin confirming the Customer was subscribed to the Form.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], 'Customer subscribed to the Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ' [' . $_ENV['CONVERTKIT_API_FORM_ID'] . ']');
	
		// Check that the Order's Notes include a note from the Plugin confirming the Customer was subscribed to the Sequence.
		$I->wooCommerceOrderNoteExists($I, $result['order_id'], 'Customer subscribed to the Sequence: ' . $_ENV['CONVERTKIT_API_SEQUENCE_NAME'] . ' [' . $_ENV['CONVERTKIT_API_SEQUENCE_ID'] . ']');
	}

	/**
	 * Test that the Customer is not resubscribed ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - The opt in checkbox is checked on the WooCommerce checkout, and
	 * - The Customer purchases a 'Simple' WooCommerce Product, and
	 * - The Customer is subscribed at the point the WooCommerce Order is marked as processing, and
	 * - The Customer unsubscribes from ConvertKit, and
	 * - The Order's Status is changed to a non-Processing status, and
	 * - The Order's Status is changed back to Processing.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testCustomerIsNotResubscribedWhenOrderStatusChanges(AcceptanceTester $I)
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
		$subscriber = $I->apiCheckSubscriberExists($I, $result['email_address']);

		// Confirm the subscriber's custom field data is empty, as no Order to Custom Field mapping was specified
		// in the integration's settings.
		$I->apiCustomFieldDataIsEmpty($I, $subscriber);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($result['email_address']);

		// Change the Order's Status to any status other than 'Processing'.
		$I->wooCommerceChangeOrderStatus($I, $result['order_id'], 'Pending payment');

		// Change the Order's Status to 'Processing'.
		$I->wooCommerceChangeOrderStatus($I, $result['order_id'], 'Processing');

		// Confirm that the email address was not added to ConvertKit a second time.
		$I->apiCheckSubscriberDoesNotExist($I, $result['email_address']);
	}
}