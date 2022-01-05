<?php
/**
 * Tests that the ConvertKit Form / Tag / Sequence selection works on
 * a WooCommerce Product.
 * 
 * @since 	1.4.2
 */
class ProductCest
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
	 * Test that selecting a Form at Product level works when creating a WooCommerce Product.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testProductFormSetting(AcceptanceTester $I)
	{
		// Navigate to Products > Add New.
		$I->amOnAdminPage('post-new.php?post_type=product');

		// Define a Title and Price.
		$I->fillField('post_title', 'ConvertKit: Product Form Setting');
		$I->fillField('_regular_price', '10');

		// Check that the ConvertKit Form/Tag/Sequence option exists.
		$I->seeElementInDOM('#ckwc_subscription');
		
		// Set Name Format = Billing First Name
		$I->selectOption('#ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Save
		$I->executeJS('window.scrollTo(0,0);'); // Otherwise button hidden behind admin bar and test fails.
		$I->click('Publish');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#ckwc_subscription', $_ENV['CONVERTKIT_API_FORM_NAME']);
	}

	/**
	 * Test that selecting a Tag at Product level works when creating a WooCommerce Product.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testProductTagSetting(AcceptanceTester $I)
	{
		// Navigate to Products > Add New.
		$I->amOnAdminPage('post-new.php?post_type=product');

		// Define a Title and Price.
		$I->fillField('post_title', 'ConvertKit: Product Tag Setting');
		$I->fillField('_regular_price', '10');

		// Check that the ConvertKit Form/Tag/Sequence option exists.
		$I->seeElementInDOM('#ckwc_subscription');
		
		// Set Name Format = Billing First Name
		$I->selectOption('#ckwc_subscription', $_ENV['CONVERTKIT_API_TAG_NAME']);

		// Save
		$I->executeJS('window.scrollTo(0,0);'); // Otherwise button hidden behind admin bar and test fails.
		$I->click('Publish');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#ckwc_subscription', $_ENV['CONVERTKIT_API_TAG_NAME']);
	}

	/**
	 * Test that selecting a Sequence at Product level works when creating a WooCommerce Product.
	 * 
	 * @since 	1.4.2
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testProductSequenceSetting(AcceptanceTester $I)
	{
		// Navigate to Products > Add New.
		$I->amOnAdminPage('post-new.php?post_type=product');

		// Define a Title and Price.
		$I->fillField('post_title', 'ConvertKit: Product Sequence Setting');
		$I->fillField('_regular_price', '10');

		// Check that the ConvertKit Form/Tag/Sequence option exists.
		$I->seeElementInDOM('#ckwc_subscription');
		
		// Set Name Format = Billing First Name
		$I->selectOption('#ckwc_subscription', $_ENV['CONVERTKIT_API_SEQUENCE_NAME']);

		// Save
		$I->executeJS('window.scrollTo(0,0);'); // Otherwise button hidden behind admin bar and test fails.
		$I->click('Publish');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the setting saved.
		$I->seeOptionIsSelected('#ckwc_subscription', $_ENV['CONVERTKIT_API_SEQUENCE_NAME']);
	}
}
