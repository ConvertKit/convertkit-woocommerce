<?php
/**
 * Tests the various settings do (or do not) subscribe the customer to a ConvertKit Form
 * or Tag when an order is placed through WooCommerce.
 * 
 * @TODO
 * 
 * @since 	1.9.6
 */
class SubscriptionCest
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
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenCheckedWithFormForOrderCreatedEvent(AcceptanceTester $I)
	{
		// Enable the Opt-In Checkbox option.
		$I->checkOption('#woocommerce_ckwc_display_opt_in');

		// Set Subscribe Event = Order Created.
		$I->selectOption('#woocommerce_ckwc_event', 'Order Created');

		// Save.
		$I->click('Save changes');

		// Define Form to subscribe the Customer to, now that the API credentials are saved and the Forms are listed.
		$I->selectOption('#woocommerce_ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Save.
		$I->click('Save changes');

		// Create Simple Product.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Define Email Address for this Test.
		$emailAddress = 'wordpress-' . $productID . '@convertkit.com';

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($emailAddress);

		// Logout as the WordPress Administrator.
		$I->logOut();

		// Add Product to Cart and load Checkout.
		$I->wooCommerceCheckoutWithProduct($I, $productID, 'Simple Product', $emailAddress);

		// Check Opt In Box.
		$I->checkOption('#ckwc_opt_in');

		// Click Place order button.
		$I->click('Place order');

		// Wait until JS completes and redirects.
		$I->waitForElement('.woocommerce-order-received', 10);
		
		// Confirm 'Order Received' is displayed
		$I->seeInSource('Order received');
		$I->seeInSource('<h2 class="woocommerce-order-details__title">Order details</h2>');

		// Confirm that the email address was added to ConvertKit.
		$I->apiCheckSubscriberExists($I, $emailAddress);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($emailAddress);
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - The opt in checkbox is checked on the WooCommerce checkout, and
	 * - The Customer is subscribed at the point the WooCommerce Order is completed.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenCheckedWithFormForOrderCompletedEvent(AcceptanceTester $I)
	{
		// Enable the Opt-In Checkbox option.
		$I->checkOption('#woocommerce_ckwc_display_opt_in');

		// Set Subscribe Event = Order Created.
		$I->selectOption('#woocommerce_ckwc_event', 'Order Completed');

		// Save.
		$I->click('Save changes');

		// Define Form to subscribe the Customer to, now that the API credentials are saved and the Forms are listed.
		$I->selectOption('#woocommerce_ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Save.
		$I->click('Save changes');

		// Create Simple Product.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Define Email Address for this Test.
		$emailAddress = 'wordpress-' . $productID . '@convertkit.com';

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($emailAddress);

		// Logout as the WordPress Administrator.
		$I->logOut();

		// Add Product to Cart and load Checkout.
		$I->wooCommerceCheckoutWithProduct($I, $productID, 'Simple Product', $emailAddress);

		// Check Opt In Box.
		$I->checkOption('#ckwc_opt_in');

		// Click Place order button.
		$I->click('Place order');

		// Wait until JS completes and redirects.
		$I->waitForElement('.woocommerce-order-received', 10);
		
		// Confirm 'Order Received' is displayed
		$I->seeInSource('Order received');
		$I->seeInSource('<h2 class="woocommerce-order-details__title">Order details</h2>');

		// Confirm that the email address was not yet added to ConvertKit.
		$I->apiCheckSubscriberDoesNotExist($I, $emailAddress);

		// Edit the Order
		$I->loginAsAdmin();
		$I->amOnAdminPage('edit.php?post_type=shop_order');
		$I->click('td.order_number a.order-view');

		// Change the Order status to Completed and submit
		$I->submitForm('form#post', [
			'order_status' => 'Completed',
		]);

		// Wait until save completes.
		$I->waitForElement('.notice-success', 10);
		$I->seeInSource('Order updated.');

		// Confirm that the email address was now added to ConvertKit.
		$I->apiCheckSubscriberExists($I, $emailAddress);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($emailAddress);
	}

	/**
	 * Test that the Customer is NOT subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - The opt in checkbox is unchecked on the WooCommerce checkout, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenUncheckedWithFormForOrderCreatedEvent(AcceptanceTester $I)
	{
		// Enable the Opt-In Checkbox option.
		$I->checkOption('#woocommerce_ckwc_display_opt_in');

		// Set Subscribe Event = Order Created.
		$I->selectOption('#woocommerce_ckwc_event', 'Order Created');

		// Save.
		$I->click('Save changes');

		// Define Form to subscribe the Customer to, now that the API credentials are saved and the Forms are listed.
		$I->selectOption('#woocommerce_ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Save.
		$I->click('Save changes');

		// Create Simple Product.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Define Email Address for this Test.
		$emailAddress = 'wordpress-' . $productID . '@convertkit.com';

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($emailAddress);

		// Logout as the WordPress Administrator.
		$I->logOut();

		// Add Product to Cart and load Checkout.
		$I->wooCommerceCheckoutWithProduct($I, $productID, 'Simple Product', $emailAddress);

		// Uncheck Opt In Box.
		$I->uncheckOption('#ckwc_opt_in');

		// Click Place order button.
		$I->click('Place order');

		// Wait until JS completes and redirects.
		$I->waitForElement('.woocommerce-order-received', 10);
		
		// Confirm 'Order Received' is displayed
		$I->seeInSource('Order received');
		$I->seeInSource('<h2 class="woocommerce-order-details__title">Order details</h2>');

		// Confirm that the email address was not added to ConvertKit.
		$I->apiCheckSubscriberDoesNotExist($I, $emailAddress);
	}

	/**
	 * Test that the Customer is NOT subscribed to ConvertKit when:
	 * - The opt in checkbox is enabled in the integration Settings, and
	 * - The opt in checkbox is unchecked on the WooCommerce checkout, and
	 * - The Customer is subscribed at the point the WooCommerce Order is completed.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInWhenUncheckedWithFormForOrderCompletedEvent(AcceptanceTester $I)
	{
		// Enable the Opt-In Checkbox option.
		$I->checkOption('#woocommerce_ckwc_display_opt_in');

		// Set Subscribe Event = Order Created.
		$I->selectOption('#woocommerce_ckwc_event', 'Order Completed');

		// Save.
		$I->click('Save changes');

		// Define Form to subscribe the Customer to, now that the API credentials are saved and the Forms are listed.
		$I->selectOption('#woocommerce_ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Save.
		$I->click('Save changes');

		// Create Simple Product.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Define Email Address for this Test.
		$emailAddress = 'wordpress-' . $productID . '@convertkit.com';

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($emailAddress);

		// Logout as the WordPress Administrator.
		$I->logOut();

		// Add Product to Cart and load Checkout.
		$I->wooCommerceCheckoutWithProduct($I, $productID, 'Simple Product', $emailAddress);

		// Uncheck Opt In Box.
		$I->uncheckOption('#ckwc_opt_in');

		// Click Place order button.
		$I->click('Place order');

		// Wait until JS completes and redirects.
		$I->waitForElement('.woocommerce-order-received', 10);
		
		// Confirm 'Order Received' is displayed
		$I->seeInSource('Order received');
		$I->seeInSource('<h2 class="woocommerce-order-details__title">Order details</h2>');

		// Confirm that the email address was not yet added to ConvertKit.
		$I->apiCheckSubscriberDoesNotExist($I, $emailAddress);

		// Edit the Order
		$I->loginAsAdmin();
		$I->amOnAdminPage('edit.php?post_type=shop_order');
		$I->click('td.order_number a.order-view');

		// Change the Order status to Completed and submit
		$I->submitForm('form#post', [
			'order_status' => 'Completed',
		]);

		// Wait until save completes.
		$I->waitForElement('.notice-success', 10);
		$I->seeInSource('Order updated.');

		// Confirm that the email address was not added to ConvertKit.
		$I->apiCheckSubscriberDoesNotExist($I, $emailAddress);
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is disabled in the integration Settings, and
	 * - The Customer is subscribed at the point the WooCommerce Order is created.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInDisabledWithFormForOrderCreatedEvent(AcceptanceTester $I)
	{
		// Disable the Opt-In Checkbox option.
		$I->uncheckOption('#woocommerce_ckwc_display_opt_in');

		// Set Subscribe Event = Order Created.
		$I->selectOption('#woocommerce_ckwc_event', 'Order Created');

		// Save.
		$I->click('Save changes');

		// Define Form to subscribe the Customer to, now that the API credentials are saved and the Forms are listed.
		$I->selectOption('#woocommerce_ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Save.
		$I->click('Save changes');

		// Create Simple Product.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Define Email Address for this Test.
		$emailAddress = 'wordpress-' . $productID . '@convertkit.com';

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($emailAddress);

		// Logout as the WordPress Administrator.
		$I->logOut();

		// Add Product to Cart and load Checkout.
		$I->wooCommerceCheckoutWithProduct($I, $productID, 'Simple Product', $emailAddress);

		// Click Place order button.
		$I->click('Place order');

		// Wait until JS completes and redirects.
		$I->waitForElement('.woocommerce-order-received', 10);
		
		// Confirm 'Order Received' is displayed
		$I->seeInSource('Order received');
		$I->seeInSource('<h2 class="woocommerce-order-details__title">Order details</h2>');

		// Confirm that the email address was added to ConvertKit.
		$I->apiCheckSubscriberExists($I, $emailAddress);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($emailAddress);
	}

	/**
	 * Test that the Customer is subscribed to ConvertKit when:
	 * - The opt in checkbox is disabled in the integration Settings, and
	 * - The Customer is subscribed at the point the WooCommerce Order is completed.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testOptInDisabledWithFormForOrderCompletedEvent(AcceptanceTester $I)
	{
		// Disable the Opt-In Checkbox option.
		$I->uncheckOption('#woocommerce_ckwc_display_opt_in');

		// Set Subscribe Event = Order Created.
		$I->selectOption('#woocommerce_ckwc_event', 'Order Completed');

		// Save.
		$I->click('Save changes');

		// Define Form to subscribe the Customer to, now that the API credentials are saved and the Forms are listed.
		$I->selectOption('#woocommerce_ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Save.
		$I->click('Save changes');

		// Create Simple Product.
		$productID = $I->wooCommerceCreateSimpleProduct($I);

		// Define Email Address for this Test.
		$emailAddress = 'wordpress-' . $productID . '@convertkit.com';

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($emailAddress);

		// Logout as the WordPress Administrator.
		$I->logOut();

		// Add Product to Cart and load Checkout.
		$I->wooCommerceCheckoutWithProduct($I, $productID, 'Simple Product', $emailAddress);

		// Check Opt In Box.
		$I->checkOption('#ckwc_opt_in');

		// Click Place order button.
		$I->click('Place order');

		// Wait until JS completes and redirects.
		$I->waitForElement('.woocommerce-order-received', 10);
		
		// Confirm 'Order Received' is displayed
		$I->seeInSource('Order received');
		$I->seeInSource('<h2 class="woocommerce-order-details__title">Order details</h2>');

		// Confirm that the email address was not yet added to ConvertKit.
		$I->apiCheckSubscriberDoesNotExist($I, $emailAddress);

		// Edit the Order
		$I->loginAsAdmin();
		$I->amOnAdminPage('edit.php?post_type=shop_order');
		$I->click('td.order_number a.order-view');

		// Change the Order status to Completed and submit
		$I->submitForm('form#post', [
			'order_status' => 'Completed',
		]);

		// Wait until save completes.
		$I->waitForElement('.notice-success', 10);
		$I->seeInSource('Order updated.');

		// Confirm that the email address was now added to ConvertKit.
		$I->apiCheckSubscriberExists($I, $emailAddress);

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($emailAddress);
	}

	
}