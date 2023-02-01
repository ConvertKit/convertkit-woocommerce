<?php
/**
 * Tests for the CKWC_API class.
 *
 * @since   1.5.7
 */
class APITest extends \Codeception\TestCase\WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the ConvertKit API class.
	 *
	 * @since   1.5.7
	 *
	 * @var     CKWC_API
	 */
	private $api;

	/**
	 * Performs actions before each test.
	 *
	 * @since   1.5.7
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin, to include the Plugin's constants in tests.
		activate_plugins('convertkit-woocommerce/woocommerce-convertkit.php');

		// Include class from /includes to test, as they won't be loaded by the Plugin
		// because WooCommerce is not active.
		require_once 'includes/class-ckwc-api.php';

		// Initialize the classes we want to test.
		$this->api = new CKWC_API( $_ENV['CONVERTKIT_API_KEY'], $_ENV['CONVERTKIT_API_SECRET'] );
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   1.5.7
	 */
	public function tearDown(): void
	{
		// Destroy the classes we tested.
		unset($this->api);

		parent::tearDown();
	}

	/**
	 * Test that the User Agent string is in the expected format and
	 * includes the Plugin's name and version number.
	 *
	 * @since   1.5.7
	 */
	public function testUserAgent()
	{
		// When an API call is made, inspect the user-agent argument.
		add_filter(
			'http_request_args',
			function($args, $url) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
				$this->assertStringContainsString(
					CKWC_PLUGIN_NAME . '/' . CKWC_PLUGIN_VERSION,
					$args['user-agent']
				);
				return $args;
			},
			10,
			2
		);

		// Perform a request.
		$result = $this->api->account();
	}
}
