<?php
namespace Helper\Acceptance;

/**
 * Helper methods and actions related to WooCommerce,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   1.0.0
 */
class WooCommerce extends \Codeception\Module
{
	/**
	 * Helper method to setup the WooCommerce Plugin.
	 *
	 * @since   1.0.0
	 *
	 * @param   AcceptanceTester $I     AcceptanceTester.
	 */
	public function setupWooCommercePlugin($I)
	{
		// Setup Cash on Delivery as Payment Method.
		$I->haveOptionInDatabase(
			'woocommerce_cod_settings',
			[
				'enabled'            => 'yes',
				'title'              => 'Cash on delivery',
				'description'        => 'Pay with cash upon delivery',
				'instructions'       => 'Pay with cash upon delivery',
				'enable_for_methods' => [],
				'enable_for_virtual' => 'yes',
			]
		);

		// Setup Stripe as Payment Method, as it's required for subscription products.
		$I->haveOptionInDatabase(
			'woocommerce_stripe_settings',
			[
				'enabled'                               => 'yes',
				'title'                                 => 'Credit Card (Stripe)',
				'description'                           => 'Pay with your credit card via Stripe.',
				'api_credentials'                       => '',
				'testmode'                              => 'yes',
				'test_publishable_key'                  => $_ENV['STRIPE_TEST_PUBLISHABLE_KEY'],
				'test_secret_key'                       => $_ENV['STRIPE_TEST_SECRET_KEY'],
				'publishable_key'                       => '',
				'secret_key'                            => '',
				'webhook'                               => '',
				'test_webhook_secret'                   => '',
				'webhook_secret'                        => '',
				'inline_cc_form'                        => 'yes', // Required so one iframe is output by Stripe, instead of 3.
				'statement_descriptor'                  => '',
				'capture'                               => 'yes',
				'payment_request'                       => 'no',
				'payment_request_button_type'           => 'buy',
				'payment_request_button_theme'          => 'dark',
				'payment_request_button_locations'      => [
					'checkout',
				],
				'payment_request_button_size'           => 'default',
				'saved_cards'                           => 'no',
				'logging'                               => 'no',
				'upe_checkout_experience_enabled'       => 'disabled',
				'title_upe'                             => '',
				'is_short_statement_descriptor_enabled' => 'no',
				'upe_checkout_experience_accepted_payments' => [],
				'short_statement_descriptor'            => 'CK',
			]
		);
	}

	/**
	 * Helper method to setup HPOS in WooCommerce.
	 *
	 * @since   1.6.6
	 *
	 * @param   AcceptanceTester $I     AcceptanceTester.
	 */
	public function setupWooCommerceHPOS($I)
	{
		$I->amOnAdminPage('admin.php?page=wc-settings&tab=advanced&section=features');
		$I->checkOption('woocommerce_feature_custom_order_tables_enabled');
		$I->click('Save changes');
		$I->amOnAdminPage('admin.php?page=wc-settings&tab=advanced&section=custom_data_stores');
		$I->selectOption('input[name="woocommerce_custom_orders_table_enabled"]', 'yes');
		$I->click('Save changes');
	}

	/**
	 * Helper method to setup the Custom Order Numbers Plugin.
	 *
	 * @since   1.0.0
	 *
	 * @param   AcceptanceTester $I     AcceptanceTester.
	 */
	public function setupCustomOrderNumbersPlugin($I)
	{
		// Setup WooCommerce Order Number prefix based on the current date and PHP version.
		$I->haveOptionInDatabase('alg_wc_custom_order_numbers_prefix', 'ckwc-' . date( 'Y-m-d-H-i-s' ) . '-php-' . PHP_VERSION_ID . '-');
	}

	/**
	 * Helper method to:
	 * - configure the Plugin's opt in, subscribe event and purchase options,
	 * - create a WooCommerce Product (simple|virtual|zero|subscription)
	 * - log out as the WordPress Administrator
	 * - add the WooCommerce Product to the cart
	 * - complete checkout
	 *
	 * This is quite a monolithic function, however this flow is used across 20+ tests,
	 * so it's better to have the code here than in every single test.
	 *
	 * @since   1.9.6
	 *
	 * @param   AcceptanceTester $I                          AcceptanceTester.
	 * @param   string           $productType                WooCommerce Product Type (simple|virtual|zero|subscription).
	 * @param   bool             $displayOptIn               Display Opt In on Checkout.
	 * @param   bool             $checkOptIn                 Check Opt In checkbox on Checkout.
	 * @param   mixed            $pluginFormTagSequence      Plugin Setting for Form, Tag or Sequence to subscribe the Customer to.
	 * @param   mixed            $subscriptionEvent          Subscription event setting.
	 * @param   bool             $sendPurchaseData           Send WooCommerce Order data to ConvertKit Purchase Data API.
	 * @param   mixed            $productFormTagSequence     Product Setting for Form, Tag or Sequence to subscribe the Customer to.
	 * @param   bool             $customFields               Map WooCommerce fields to ConvertKit Custom Fields.
	 * @param   string           $nameFormat                 Name format.
	 * @param   mixed            $couponFormTagSequence      Coupon Setting for Form, Tag or Sequence to subscribe the Customer to.
	 */
	public function wooCommerceCreateProductAndCheckoutWithConfig(
		$I,
		$productType = 'simple',
		$displayOptIn = false,
		$checkOptIn = false,
		$pluginFormTagSequence = false,
		$subscriptionEvent = false,
		$sendPurchaseData = false,
		$productFormTagSequence = false,
		$customFields = false,
		$nameFormat = 'first',
		$couponFormTagSequence = false
	)
	{
		// Setup ConvertKit for WooCommerce Plugin.
		$I->setupConvertKitPlugin(
			$I,
			$_ENV['CONVERTKIT_API_KEY'],
			$_ENV['CONVERTKIT_API_SECRET'],
			$subscriptionEvent,
			$pluginFormTagSequence,
			$nameFormat,
			$customFields,
			$displayOptIn,
			( ( $sendPurchaseData === true ) ? 'processing' : $sendPurchaseData )
		);

		// Create Product.
		switch ($productType) {
			case 'zero':
				$productName   = 'Zero Value Product';
				$paymentMethod = 'cod';
				$productID     = $I->wooCommerceCreateZeroValueProduct($I, $productFormTagSequence);
				break;

			case 'virtual':
				$productName   = 'Virtual Product';
				$paymentMethod = 'cod';
				$productID     = $I->wooCommerceCreateVirtualProduct($I, $productFormTagSequence);
				break;

			case 'subscription':
				$productName   = 'Subscription Product';
				$paymentMethod = 'stripe';
				$productID     = $I->wooCommerceCreateSubscriptionProduct($I, $productFormTagSequence);
				break;

			case 'simple':
				$productName   = 'Simple Product';
				$paymentMethod = 'cod';
				$productID     = $I->wooCommerceCreateSimpleProduct($I, $productFormTagSequence);
				break;
		}

		// Create Coupon.
		if ($couponFormTagSequence) {
			$couponID = $I->wooCommerceCreateCoupon($I, '20off', $couponFormTagSequence);
		}

		// Define Email Address for this Test.
		$emailAddress = $I->generateEmailAddress();

		// Unsubscribe the email address, so we restore the account back to its previous state.
		$I->apiUnsubscribe($emailAddress);

		// Logout as the WordPress Administrator.
		$I->logOut();

		// Add Product to Cart and load Checkout.
		$I->wooCommerceCheckoutWithProduct($I, $productID, $productName, $emailAddress, $paymentMethod);

		// Apply Coupon Code.
		if (isset($couponID)) {
			$I->click('a.showcoupon');
			$I->waitForElementNotVisible('.blockOverlay');
			$I->waitForElementVisible('input#coupon_code');
			$I->fillField('input#coupon_code', '20off');
			$I->click('Apply coupon');
			$I->waitForText('Coupon code applied successfully.', 5, '.woocommerce-message');
		}

		// Handle Opt-In Checkbox.
		if ($displayOptIn) {
			if ($checkOptIn) {
				$I->checkOption('#ckwc_opt_in');
			} else {
				$I->uncheckOption('#ckwc_opt_in');
			}
		} else {
			$I->dontSeeElement('#ckwc_opt_in');
		}

		// Click Place order button.
		$I->waitForElementNotVisible('.blockOverlay');
		$I->click('#place_order');

		// Confirm order received is displayed.
		// WooCommerce changed the default wording between 5.x and 6.x, so perform
		// a few checks to be certain.
		$I->waitForElement('body.woocommerce-order-received');
		$I->seeInSource('Order');
		$I->seeInSource('received');
		$I->seeInSource('<h2 class="woocommerce-order-details__title">Order details</h2>');

		// Return data.
		return [
			'email_address'   => $emailAddress,
			'product_id'      => $productID,
			'order_id'        => $I->grabTextFrom('.woocommerce-order-overview__order strong'),
			'subscription_id' => ( ( $productType === 'subscription' ) ? (int) filter_var($I->grabTextFrom('.woocommerce-orders-table__cell-order-number a'), FILTER_SANITIZE_NUMBER_INT) : 0 ),
		];
	}

	/**
	 * Changes the order status for the given Order ID to the given Order Status.
	 *
	 * @since   1.9.6
	 *
	 * @param   AcceptanceTester $I              Acceptance Tester.
	 * @param   int              $orderID        WooCommerce Order ID.
	 * @param   string           $orderStatus    Order Status.
	 */
	public function wooCommerceChangeOrderStatus($I, $orderID, $orderStatus)
	{
		// We perform the order status change by editing the Order as a WordPress Administrator would,
		// so that WooCommerce triggers its actions and filters that our integration hooks into.
		$I->loginAsAdmin();

		// If the Order ID contains dashes, it's prefixed by the Custom Order Numbers Plugin.
		if (strpos($orderID, '-') !== false) {
			$orderIDParts = explode('-', $orderID);
			$orderID      = $orderIDParts[ count($orderIDParts) - 1 ];
		}

		$I->amOnAdminPage('post.php?post=' . $orderID . '&action=edit');
		$I->submitForm(
			'div.wrap > form',
			[
				'order_status' => $orderStatus,
			]
		);
	}

	/**
	 * Creates a 'Simple product' in WooCommerce that can be used for tests.
	 *
	 * @since   1.0.0
	 *
	 * @param   AcceptanceTester $I                      Acceptance Tester.
	 * @param   mixed            $productFormTagSequence Product Setting for Form, Tag or Sequence to subscribe the Customer to.
	 * @return  int                                         Product ID
	 */
	public function wooCommerceCreateSimpleProduct($I, $productFormTagSequence = false)
	{
		return $I->havePostInDatabase(
			[
				'post_type'    => 'product',
				'post_status'  => 'publish',
				'post_name'    => 'simple-product',
				'post_title'   => 'Simple Product',
				'post_content' => 'Simple Product Content',
				'meta_input'   => [
					'_backorders'        => 'no',
					'_download_expiry'   => -1,
					'_download_limit'    => -1,
					'_downloadable'      => 'no',
					'_manage_stock'      => 'no',
					'_price'             => 10,
					'_product_version'   => '6.3.0',
					'_regular_price'     => 10,
					'_sold_individually' => 'no',
					'_stock'             => null,
					'_stock_status'      => 'instock',
					'_tax_class'         => '',
					'_tax_status'        => 'taxable',
					'_virtual'           => 'no',
					'_wc_average_rating' => 0,
					'_wc_review_count'   => 0,

					// ConvertKit Integration Form/Tag/Sequence.
					'ckwc_subscription'  => ( $productFormTagSequence ? $productFormTagSequence : '' ),
				],
			]
		);
	}

	/**
	 * Creates a 'Simple product' in WooCommerce that is set to be 'Virtual', that can be used for tests.
	 *
	 * @since   1.0.0
	 *
	 * @param   AcceptanceTester $I                      Acceptance Tester.
	 * @param   mixed            $productFormTagSequence Product Setting for Form, Tag or Sequence to subscribe the Customer to.
	 * @return  int                                         Product ID
	 */
	public function wooCommerceCreateVirtualProduct($I, $productFormTagSequence = false)
	{
		return $I->havePostInDatabase(
			[
				'post_type'    => 'product',
				'post_status'  => 'publish',
				'post_name'    => 'virtual-product',
				'post_title'   => 'Virtual Product',
				'post_content' => 'Virtual Product Content',
				'meta_input'   => [
					'_backorders'        => 'no',
					'_download_expiry'   => -1,
					'_download_limit'    => -1,
					'_downloadable'      => 'no',
					'_manage_stock'      => 'no',
					'_price'             => 10,
					'_product_version'   => '6.3.0',
					'_regular_price'     => 10,
					'_sold_individually' => 'no',
					'_stock'             => null,
					'_stock_status'      => 'instock',
					'_tax_class'         => '',
					'_tax_status'        => 'taxable',
					'_virtual'           => 'yes',
					'_wc_average_rating' => 0,
					'_wc_review_count'   => 0,

					// ConvertKit Integration Form/Tag/Sequence.
					'ckwc_subscription'  => ( $productFormTagSequence ? $productFormTagSequence : '' ),
				],
			]
		);
	}

	/**
	 * Creates a 'Subscription product' in WooCommerce that can be used for tests, which
	 * is set to renew daily.
	 *
	 * @since   1.4.4
	 *
	 * @param   AcceptanceTester $I                      Acceptance Tester.
	 * @param   mixed            $productFormTagSequence Product Setting for Form, Tag or Sequence to subscribe the Customer to.
	 * @return  int                                         Product ID
	 */
	public function wooCommerceCreateSubscriptionProduct($I, $productFormTagSequence = false)
	{
		return $I->havePostInDatabase(
			[
				'post_type'    => 'product',
				'post_status'  => 'publish',
				'post_name'    => 'subscription-product',
				'post_title'   => 'Subscription Product',
				'post_content' => 'Subscription Product Content',
				'meta_input'   => [
					'_backorders'                     => 'no',
					'_download_expiry'                => -1,
					'_download_limit'                 => -1,
					'_downloadable'                   => 'yes',
					'_manage_stock'                   => 'no',
					'_price'                          => 10,
					'_product_version'                => '6.2.0',
					'_regular_price'                  => 10,
					'_sold_individually'              => 'no',
					'_stock'                          => null,
					'_stock_status'                   => 'instock',
					'_subscription_length'            => 0,
					'_subscription_limit'             => 'no',
					'_subscription_one_time_shipping' => 'no',
					'_subscription_payment_sync_date' => 0,
					'_subscription_period'            => 'day',
					'_subscription_period_interval'   => 1,
					'_subscription_price'             => 10,
					'_subscription_sign_up_fee'       => 0,
					'_subscription_trial_length'      => 0,
					'_subscription_trial_period'      => 'day',
					'_tax_class'                      => '',
					'_tax_status'                     => 'taxable',
					'_virtual'                        => 'yes',
					'_wc_average_rating'              => 0,
					'_wc_review_count'                => 0,

					// ConvertKit Integration Form/Tag/Sequence.
					'ckwc_subscription'               => ( $productFormTagSequence ? $productFormTagSequence : '' ),
				],
				'tax_input'    => [
					[ 'product_type' => 'subscription' ],
				],
			]
		);
	}

	/**
	 * Creates a zero value 'Simple product' in WooCommerce that can be used for tests.
	 *
	 * @since   1.0.0
	 *
	 * @param   AcceptanceTester $I                      Acceptance Tester.
	 * @param   mixed            $productFormTagSequence Product Setting for Form, Tag or Sequence to subscribe the Customer to.
	 * @return  int                                         Product ID
	 */
	public function wooCommerceCreateZeroValueProduct($I, $productFormTagSequence = false)
	{
		return $I->havePostInDatabase(
			[
				'post_type'    => 'product',
				'post_status'  => 'publish',
				'post_name'    => 'zero-value-product',
				'post_title'   => 'Zero Value Product',
				'post_content' => 'Zero Value Product Content',
				'meta_input'   => [
					'_backorders'        => 'no',
					'_download_expiry'   => -1,
					'_download_limit'    => -1,
					'_downloadable'      => 'no',
					'_manage_stock'      => 'no',
					'_price'             => 0,
					'_product_version'   => '6.3.0',
					'_regular_price'     => 0,
					'_sold_individually' => 'no',
					'_stock'             => null,
					'_stock_status'      => 'instock',
					'_tax_class'         => '',
					'_tax_status'        => 'taxable',
					'_virtual'           => 'no',
					'_wc_average_rating' => 0,
					'_wc_review_count'   => 0,

					// ConvertKit Integration Form/Tag/Sequence.
					'ckwc_subscription'  => ( $productFormTagSequence ? $productFormTagSequence : '' ),
				],
			]
		);
	}

	/**
	 * Creates a Coupon in WooCommerce that can be used for tests.
	 *
	 * @since   1.5.9
	 *
	 * @param   AcceptanceTester $I                      Acceptance Tester.
	 * @param   string           $couponCode             Couponn Code.
	 * @param   mixed            $couponFormTagSequence  Coupon Setting for Form, Tag or Sequence to subscribe the Customer to.
	 * @return  int                                      Coupon ID
	 */
	public function wooCommerceCreateCoupon($I, $couponCode, $couponFormTagSequence = false)
	{
		return $I->havePostInDatabase(
			[
				'post_type'    => 'shop_coupon',
				'post_status'  => 'publish',
				'post_name'    => $couponCode,
				'post_title'   => $couponCode,
				'post_content' => $couponCode,
				'meta_input'   => [
					// Create a 20% off coupon. The amount doesn't matter for tests.
					'discount_type'          => 'percent',
					'coupon_amount'          => 20,
					'individual_use'         => 'no',
					'usage_limit'            => 0,
					'usage_limit_per_user'   => 0,
					'limit_usage_to_x_items' => 0,
					'usage_count'            => 0,
					'date_expires'           => null,
					'free_shipping'          => 'no',
					'exclude_sales_items'    => 'no',

					// ConvertKit Integration Form/Tag/Sequence.
					'ckwc_subscription'      => ( $couponFormTagSequence ? $couponFormTagSequence : '' ),
				],
			]
		);
	}

	/**
	 * Adds the given Product ID to the Cart, loading the Checkout screen
	 * and prefilling the standard WooCommerce Billing Fields.
	 *
	 * @since   1.0.0
	 *
	 * @param   AcceptanceTester $I              AcceptanceTester.
	 * @param   string           $productID      Product ID.
	 * @param   string           $productName    Product Name.
	 * @param   string           $emailAddress   Email Address (wordpress@convertkit.com).
	 * @param   string           $paymentMethod  Payment Method (cod|stripe).
	 */
	public function wooCommerceCheckoutWithProduct($I, $productID, $productName, $emailAddress = 'wordpress@convertkit.com', $paymentMethod = 'cod')
	{
		// Load the Product on the frontend site.
		$I->amOnPage('/?p=' . $productID);

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
		$I->fillField('#order_comments', 'Notes');

		// Depending on the payment method required, complete some fields.
		switch ($paymentMethod) {
			/**
			 * Card
			 */
			case 'stripe':
				// Complete Credit Card Details.
				$I->click('label[for="payment_method_stripe"]');
				$I->switchToIFrame('iframe[name^="__privateStripeFrame"]'); // Switch to Stripe iFrame.
				$I->fillField('cardnumber', '4242424242424242');
				$I->fillfield('exp-date', '01/26');
				$I->fillField('cvc', '123');
				$I->switchToIFrame(); // Switch back to main window.
				break;

			/**
			 * COD
			 */
			default:
				// COD is selected by default, so no need to click anything.
				break;
		}
	}

	/**
	 * Creates an Order as if the user were creating an Order through the WordPress Administration
	 * interface.
	 *
	 * @since   1.0.0
	 *
	 * @param   AcceptanceTester $I              Acceptance Tester.
	 * @param   int              $productID      Product ID.
	 * @param   string           $productName    Product Name.
	 * @param   string           $orderStatus    Order Status.
	 * @param   string           $paymentMethod  Payment Method.
	 * @return  int                                 Order ID
	 */
	public function wooCommerceCreateManualOrder($I, $productID, $productName, $orderStatus, $paymentMethod)
	{
		// Login as Administrator.
		$I->loginAsAdmin();

		// Define Email Address for this Manual Order.
		$emailAddress = $I->generateEmailAddress();

		// Create User for this Manual Order.
		$userID = $I->haveUserInDatabase(
			'test',
			'subscriber',
			[
				'user_email' => $emailAddress,
			]
		);

		// Load New Order screen.
		$I->amOnAdminPage('post-new.php?post_type=shop_order');

		// Check that no WooCommerce, PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Define Order Status.
		$I->selectOption('#order_status', $orderStatus);

		// Define User and Payment Method.
		$I->fillSelect2Field($I, '#select2-customer_user-container', $emailAddress, 'aria-owns');
		$I->selectOption('#_payment_method', $paymentMethod);

		// Add Product.
		$I->click('button.add-line-item');
		$I->click('button.add-order-item');
		$I->fillSelect2Field($I, '.wc-backbone-modal-content .select2-selection__rendered', $productName, 'aria-owns');
		$I->click('#btn-ok');

		// Create Order.
		$I->executeJS('window.scrollTo(0,0);');
		$I->click('button.save_order');

		// Check that no WooCommerce, PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Determine the Order ID.
		$orderID = $I->grabTextFrom('h2.woocommerce-order-data__heading');
		$orderID = str_replace('Order #', '', $orderID);
		$orderID = str_replace('details', '', $orderID);
		$orderID = trim($orderID);

		// Return.
		return [
			'email_address' => $emailAddress,
			'product_id'    => $productID,
			'order_id'      => $orderID,
		];
	}

	/**
	 * Check the given Order ID contains an Order Note with the given text.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I             AcceptanceTester.
	 * @param   int              $orderID       Order ID.
	 * @param   string           $noteText      Order Note Text.
	 */
	public function wooCommerceOrderNoteExists($I, $orderID, $noteText)
	{
		// Logout.
		$I->logOut();

		// Login as Administrator.
		$I->loginAsAdmin();

		// If the Order ID contains dashes, it's prefixed by the Custom Order Numbers Plugin.
		if (strpos($orderID, '-') !== false) {
			$orderIDParts = explode('-', $orderID);
			$orderID      = $orderIDParts[ count($orderIDParts) - 1 ];
		}

		// Load Edit Order screen.
		$I->amOnAdminPage('post.php?post=' . $orderID . '&action=edit');

		// Confirm note text exists.
		$I->seeInSource($noteText);
	}

	/**
	 * Check the given Order ID does not contain an Order Note with the given text.
	 *
	 * @since   1.4.2
	 *
	 * @param   AcceptanceTester $I             AcceptanceTester.
	 * @param   int              $orderID       Order ID.
	 * @param   string           $noteText      Order Note Text.
	 */
	public function wooCommerceOrderNoteDoesNotExist($I, $orderID, $noteText)
	{
		// Login as Administrator.
		$I->loginAsAdmin();

		// If the Order ID contains dashes, it's prefixed by the Custom Order Numbers Plugin.
		if (strpos($orderID, '-') !== false) {
			$orderIDParts = explode('-', $orderID);
			$orderID      = $orderIDParts[ count($orderIDParts) - 1 ];
		}

		// Load Edit Order screen.
		$I->amOnAdminPage('post.php?post=' . $orderID . '&action=edit');

		// Confirm note text does not exist.
		$I->dontSeeInSource($noteText);
	}

	/**
	 * Check the given Order ID has the given meta key.
	 *
	 * @since   1.6.6
	 *
	 * @param   AcceptanceTester $I             AcceptanceTester.
	 * @param   int              $orderID       Order ID.
	 * @param   string           $metaKey       Meta Key.
	 * @param   bool             $hposEnabled   If HPOS is enabled.
	 */
	public function wooCommerceOrderMetaKeyExists($I, $orderID, $metaKey, $hposEnabled = false)
	{
		// If HPOS is enabled, check the wp_wc_orders_meta table instead, as the Post
		// Meta isn't used.
		if ( ! $hposEnabled) {
			$I->seePostMetaInDatabase(
				[
					'post_id'  => $orderID,
					'meta_key' => $metaKey,
				]
			);
		} else {
			$I->seeInDatabase(
				'wp_wc_orders_meta',
				[
					'post_id'  => $orderID,
					'meta_key' => $metaKey,
				]
			);
		}
	}

	/**
	 * Helper method to delete all orders from the wp_posts and wp_wc_orders tables,
	 *
	 * @since   1.6.6
	 *
	 * @param   AcceptanceTester $I             AcceptanceTester.
	 */
	public function wooCommerceDeleteAllOrders($I)
	{
		// Delete from wp_posts.
		$I->dontHavePostInDatabase([ 'post_type' => 'shop_order' ]);

		// Delete from wp_wc_orders and wp_wc_orders_meta HPOS tables.
		$I->dontHaveInDatabase('wp_wc_orders', [ 'parent_order_id' => 0 ]);
		$I->dontHaveInDatabase('wp_wc_orders_meta', [ 'id >=', '0' ]);
	}
}
