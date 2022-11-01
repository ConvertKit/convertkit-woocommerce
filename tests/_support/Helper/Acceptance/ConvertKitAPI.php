<?php
namespace Helper\Acceptance;

/**
 * Helper methods and actions related to the ConvertKit API,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   1.4.2
 */
class ConvertKitAPI extends \Codeception\Module
{
	/**
	 * Check the given email address exists as a subscriber on ConvertKit.
	 *
	 * @param   AcceptanceTester $I             AcceptanceTester.
	 * @param   string           $emailAddress   Email Address.
	 * @param   mixed            $firstName      Name (false = don't check name matches).
	 * @return  array                           Subscriber
	 */
	public function apiCheckSubscriberExists($I, $emailAddress, $firstName = false)
	{
		// Run request.
		$results = $this->apiRequest(
			'subscribers',
			'GET',
			[
				'email_address' => $emailAddress,
			]
		);

		// Check at least one subscriber was returned and it matches the email address.
		$I->assertGreaterThan(0, $results['total_subscribers']);
		$I->assertEquals($emailAddress, $results['subscribers'][0]['email_address']);

		// If defined, check that the name matches for the subscriber.
		if ($firstName) {
			$I->assertEquals($firstName, $results['subscribers'][0]['first_name']);
		}

		return $results['subscribers'][0];
	}

	/**
	 * Check the given email address does not exists as a subscriber.
	 *
	 * @param   AcceptanceTester $I             AcceptanceTester.
	 * @param   string           $emailAddress   Email Address.
	 */
	public function apiCheckSubscriberDoesNotExist($I, $emailAddress)
	{
		// Run request.
		$results = $this->apiRequest(
			'subscribers',
			'GET',
			[
				'email_address' => $emailAddress,
			]
		);

		// Check no subscribers are returned by this request.
		$I->assertEquals(0, $results['total_subscribers']);
	}

	/**
	 * Check the given email address and name exists as a subscriber on ConvertKit.
	 *
	 * @param   AcceptanceTester $I             AcceptanceTester.
	 * @param   string           $emailAddress   Email Address.
	 * @param   string           $name           Name.
	 */
	public function apiCheckSubscriberEmailAndNameExists($I, $emailAddress, $name)
	{
		// Run request.
		$results = $this->apiRequest(
			'subscribers',
			'GET',
			[
				'email_address' => $emailAddress,
			]
		);

		// Check at least one subscriber was returned and it matches the email address.
		$I->assertGreaterThan(0, $results['total_subscribers']);
		$I->assertEquals($emailAddress, $results['subscribers'][0]['email_address']);

		// Check that the first_name matches the given name.
		$I->assertEquals($name, $results['subscribers'][0]['first_name']);
	}

	/**
	 * Check the given order ID exists as a purchase on ConvertKit.
	 *
	 * @param   AcceptanceTester $I             AcceptanceTester.
	 * @param   int              $orderID        Order ID.
	 * @param   string           $emailAddress   Email Address.
	 * @param   int              $productID      Product ID.
	 */
	public function apiCheckPurchaseExists($I, $orderID, $emailAddress, $productID)
	{
		// Run request.
		$purchase = $this->apiExtractPurchaseFromPurchases($this->apiGetPurchases(), $orderID);

		// Check data returned for this Order ID.
		$I->assertIsArray($purchase);
		$I->assertEquals($orderID, $purchase['transaction_id']);
		$I->assertEquals($emailAddress, $purchase['email_address']);

		// Iterate through the array of products, to find a pid matching the Product ID.
		$productExistsInPurchase = false;
		foreach ($purchase['products'] as $product) {
			if ($productID == $product['pid']) {
				$productExistsInPurchase = true;
				break;
			}
		}

		// Check that the Product exists in the purchase data.
		$I->assertTrue($productExistsInPurchase);
	}

	/**
	 * Check the given order ID does not exist as a purchase on ConvertKit.
	 *
	 * @param   AcceptanceTester $I             AcceptanceTester.
	 * @param   int              $orderID        Order ID.
	 * @param   string           $emailAddress   Email Address.
	 */
	public function apiCheckPurchaseDoesNotExist($I, $orderID, $emailAddress)
	{
		// Run request.
		$purchase = $this->apiExtractPurchaseFromPurchases($this->apiGetPurchases(), $orderID);

		// Check data not returned for this Order ID.
		// We check the email address, because each test will reset, meaning the Order ID will match that
		// of a previous test, and therefore the API will return data from an existing test.
		$I->assertIsArray($purchase);
		$I->assertNotEquals($emailAddress, $purchase['email_address']);
	}

	/**
	 * Returns a Purchase from the /purchases API endpoint based on the given Order ID (transaction_id).
	 *
	 * We cannot use /purchases/{id} as {id} is the ConvertKit ID, not the WooCommerce Order ID (which
	 * is stored in the transaction_id).
	 *
	 * @param   array $purchases  Purchases Data.
	 * @param   int   $orderID    Order ID.
	 * @return  array
	 */
	private function apiExtractPurchaseFromPurchases($purchases, $orderID)
	{
		// Bail if no purchases exist.
		if ( ! isset($purchases)) {
			return [
				'id'            => 0,
				'order_id'      => 0,
				'email_address' => 'no',
			];
		}

		// Iterate through purchases to find one where the transaction ID matches the order ID.
		foreach ($purchases as $purchase) {
			// Skip if order ID does not match.
			if ($purchase['transaction_id'] != $orderID) {
				continue;
			}

			return $purchase;
		}

		// No purchase exists with the given order ID. Return a blank array.
		return [
			'id'            => 0,
			'order_id'      => 0,
			'email_address' => 'no2',
		];
	}

	/**
	 * Returns all purchases from the API.
	 *
	 * @return  array
	 */
	public function apiGetPurchases()
	{
		// Get first page of purchases.
		$purchases  = $this->apiRequest('purchases', 'GET');
		$data       = $purchases['purchases'];
		$totalPages = $purchases['total_pages'];

		if ($totalPages == 1) {
			return $data;
		}

		// Get additional pages of purchases.
		for ($page = 2; $page <= $totalPages; $page++) {
			$purchases = $this->apiRequest(
				'purchases',
				'GET',
				[
					'page' => $page,
				]
			);

			$data = array_merge($data, $purchases['purchases']);
		}

		return $data;
	}

	/**
	 * Unsubscribes the given email address. Useful for clearing the API
	 * between tests.
	 *
	 * @param   string $emailAddress   Email Address.
	 */
	public function apiUnsubscribe($emailAddress)
	{
		// Run request.
		$this->apiRequest(
			'unsubscribe',
			'PUT',
			[
				'email' => $emailAddress,
			]
		);
	}

	/**
	 * Check the subscriber array's custom field data is valid.
	 *
	 * @param   AcceptanceTester $I             AcceptanceTester.
	 * @param   array            $subscriber     Subscriber from API.
	 */
	public function apiCustomFieldDataIsValid($I, $subscriber)
	{
		$I->assertEquals($subscriber['fields']['phone_number'], '123-123-1234');
		$I->assertEquals($subscriber['fields']['billing_address'], 'First Last, Address Line 1, City, CA 12345');
		$I->assertEquals($subscriber['fields']['shipping_address'], '');
		$I->assertEquals($subscriber['fields']['payment_method'], 'cod');
		$I->assertEquals($subscriber['fields']['notes'], 'Notes');
	}

	/**
	 * Check the subscriber array's custom field data is empty.
	 *
	 * @param   AcceptanceTester $I             AcceptanceTester.
	 * @param   array            $subscriber     Subscriber from API.
	 */
	public function apiCustomFieldDataIsEmpty($I, $subscriber)
	{
		$I->assertEquals($subscriber['fields']['phone_number'], '');
		$I->assertEquals($subscriber['fields']['billing_address'], '');
		$I->assertEquals($subscriber['fields']['shipping_address'], '');
		$I->assertEquals($subscriber['fields']['payment_method'], '');
		$I->assertEquals($subscriber['fields']['notes'], '');
	}

	/**
	 * Sends a request to the ConvertKit API, typically used to read an endpoint to confirm
	 * that data in an Acceptance Test was added/edited/deleted successfully.
	 *
	 * @param   string $endpoint   Endpoint.
	 * @param   string $method     Method (GET|POST|PUT).
	 * @param   array  $params     Endpoint Parameters.
	 */
	public function apiRequest($endpoint, $method = 'GET', $params = array())
	{
		// Build query parameters.
		$params = array_merge(
			$params,
			[
				'api_key'    => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
			]
		);

		// Send request.
		try {
			$client = new \GuzzleHttp\Client();
			$result = $client->request(
				$method,
				'https://api.convertkit.com/v3/' . $endpoint . '?' . http_build_query($params),
				[
					'headers' => [
						'Accept-Encoding' => 'gzip',
						'timeout'         => 5,
					],
				]
			);

			// Return JSON decoded response.
			return json_decode($result->getBody()->getContents(), true);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			return [];
		}
	}
}
