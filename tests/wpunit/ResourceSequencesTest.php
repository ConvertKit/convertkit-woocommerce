<?php
/**
 * Tests for the CKWC_Resource_Sequences class.
 * 
 * @since 	1.4.7
 */
class ResourceSequencesTest extends \Codeception\TestCase\WPTestCase
{
	/**
	 * @var \WpunitTester
	 */
	protected $tester;

	/**
	 * Holds the key name that stores settings for this Plugin.
	 * 
	 * @since 	1.4.7
	 * 
	 * @var 	string
	 */
	private $settings_key = 'woocommerce_ckwc_settings';

	/**
	 * Holds the ConvertKit Resource class.
	 * 
	 * @since 	1.4.7
	 * 
	 * @var 	ConvertKit_Resource_Sequences
	 */
	private $resource;

	/**
	 * Performs actions before each test.
	 * 
	 * @since 	1.4.7
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Enable integration, storing API Key and Secret in Plugin's settings.
		WP_CKWC_Integration()->update_option( 'enabled', 'yes' );
		WP_CKWC_Integration()->update_option( 'api_key', $_ENV['CONVERTKIT_API_KEY'] );
		WP_CKWC_Integration()->update_option( 'api_secret', $_ENV['CONVERTKIT_API_SECRET'] );

		// Initialize the resource class we want to test.
		$this->resource = new CKWC_Resource_Sequences();
		$this->assertNotInstanceOf(WP_Error::class, $this->resource->resources);
	}

	/**
	 * Performs actions after each test.
	 * 
	 * @since 	1.9.6.9
	 */
	public function tearDown(): void
	{
		// Disable integration, removing API Key and Secret from Plugin's settings.
		WP_CKWC_Integration()->update_option( 'enabled', 'no' );
		WP_CKWC_Integration()->update_option( 'api_key', '' );
		WP_CKWC_Integration()->update_option( 'api_secret', '' );

		// Delete Settings and Resources from options table.
		delete_option($this->settings_key);
		delete_option($this->resource->settings_name);
		delete_option($this->resource->settings_name . '_last_queried');
		parent::tearDown();
	}

	/**
	 * Test that the refresh() function performs as expected.
	 * 
	 * @since 	1.4.7
	 */
	public function testRefresh()
	{
		// Confirm that the data is stored in the options table and includes some expected keys.
		$result = $this->resource->refresh();
		$this->assertIsArray($result);
		$this->assertArrayHasKey('id', reset($result));
		$this->assertArrayHasKey('name', reset($result));
	}

	/**
	 * Test that the expiry timestamp is set and returns the expected value.
	 * 
	 * @since 	1.4.7
	 */
	public function testExpiry()
	{
		// Define the expected expiry date based on the resource class' $cache_duration setting.
		$expectedExpiryDate = date('Y-m-d', time() + $this->resource->cache_duration);

		// Fetch the actual expiry date set when the resource class was initialized.
		$expiryDate = date('Y-m-d', $this->resource->last_queried + $this->resource->cache_duration);

		// Confirm both dates match.
		$this->assertEquals($expectedExpiryDate, $expiryDate);
	}

	/**
	 * Test that the get() function performs as expected.
	 * 
	 * @since 	1.4.7
	 */
	public function testGet()
	{
		// Confirm that the data is fetched from the options table when using get(), and includes some expected keys.
		$result = $this->resource->get();
		$this->assertNotInstanceOf(WP_Error::class, $result);
		$this->assertIsArray($result);
		$this->assertArrayHasKey('id', reset($result));
		$this->assertArrayHasKey('name', reset($result));
	}

	/**
	 * Test that the count() function returns the number of resources.
	 * 
	 * @since 	1.4.7
	 */
	public function testCount()
	{
		$result = $this->resource->get();
		$this->assertEquals($this->resource->count(), count($result));
	}

	/**
	 * Test that the exist() function performs as expected.
	 * 
	 * @since 	1.4.7
	 */
	public function testExist()
	{
		// Confirm that the function returns true, because resources exist.
		$result = $this->resource->exist();
		$this->assertSame($result, true);
	}
}