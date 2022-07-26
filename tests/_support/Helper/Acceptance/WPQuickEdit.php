<?php
namespace Helper\Acceptance;

// Define any custom actions related to WordPress' Quick Edit functionality that
// would be used across multiple tests.
// These are then available in $I->{yourFunctionName}

class WPQuickEdit extends \Codeception\Module
{
	/**
	 * Quick Edits the given Post ID, changing form field values and saving.
	 * 
	 * @since 	1.4.8
	 * 
	 * @param 	$I 	AcceptanceHelper 	Acceptance Helper.
	 * @param 	string 	$postType 		Programmatic Post Type.
	 * @param 	int 	$postID 		Post ID.
	 * @param 	array 	$configuration 	Configuration (field => value key/value array).
	 */
	public function quickEdit($I, $postType, $postID, $configuration)
	{
		// Navigate to Post Type's WP_List_Table.
		$I->amOnAdminPage('edit.php?post_type='.$postType);

		// Hover mouse over Post's table row.
		$I->moveMouseOver('tr#post-'.$postID);

		// Wait for Quick edit link to be visible.
		$I->waitForElementVisible('tr#post-'.$postID.' button.editinline');
		
		// Click Quick Edit link.
		$I->click('tr#post-'.$postID.' button.editinline');

		// Apply configuration.
		foreach ($configuration as $field=>$attributes) {
			// Check that the field exists.
			$I->seeElementInDOM('#' . $field);
			
			// Depending on the field's type, define its value.
			switch ($attributes[0]) {
				case 'select2':
					$I->fillSelect2Field($I, '#select2-' . $field . '-container', $attributes[1]);
					break;
				case 'select':
					$I->selectOption('#' . $field, $attributes[1]);
					break;
				default:
					$I->fillField('#' . $field, $attributes[1]);
					break;
			}
		}

		// Click Update.
		$I->click('Update');
	}
}