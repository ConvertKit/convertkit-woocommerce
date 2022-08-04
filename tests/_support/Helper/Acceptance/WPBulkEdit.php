<?php
namespace Helper\Acceptance;

// Define any custom actions related to WordPress' Bulk Edit functionality that
// would be used across multiple tests.
// These are then available in $I->{yourFunctionName}

class WPBulkEdit extends \Codeception\Module
{
	/**
	 * Bulk Edits the given Post IDs, changing form field values and saving.
	 * 
	 * @since 	1.4.8
	 * 
	 * @param 	$I 	AcceptanceHelper 	Acceptance Helper.
	 * @param 	string 	$postType 		Programmatic Post Type.
	 * @param 	array 	$postIDs 		Post IDs.
	 * @param 	array 	$configuration 	Configuration (field => value key/value array).
	 */
	public function bulkEdit($I, $postType, $postIDs, $configuration)
	{
		// Open Bulk Edit form for the Posts.
		$I->openBulkEdit($I, $postType, $postIDs);

		// Apply configuration.
		foreach ($configuration as $field=>$attributes) {
			// Check that the field exists.
			$I->seeElementInDOM('#ckwc-bulk-edit #' . $field);
			
			// Depending on the field's type, define its value.
			switch ($attributes[0]) {
				case 'select':
					$I->selectOption('#ckwc-bulk-edit #' . $field, $attributes[1]);
					break;
				default:
					$I->fillField('#ckwc-bulk-edit #' . $field, $attributes[1]);
					break;
			}
		}

		// Scroll to Update button.
		$I->scrollTo('#bulk_edit');

		// Click Update.
		$I->click('Update');

		// Confirm that Bulk Editing saved with no errors.
		$I->seeInSource(count($postIDs).' '.$postType.'s updated');
	}

	/**
	 * Opens the Bulk Edit form for the given Post ID.
	 * 
	 * @since 	1.4.8
	 * 
	 * @param 	$I 	AcceptanceHelper 	Acceptance Helper.
	 * @param 	string 	$postType 		Programmatic Post Type.
	 * @param 	array 	$postIDs 		Post IDs.
	 */
	public function openBulkEdit($I, $postType, $postIDs)
	{
		// Navigate to Post Type's WP_List_Table.
		$I->amOnAdminPage('edit.php?post_type='.$postType);

		// Check boxes for Post IDs.
		foreach($postIDs as $postID) {
			$I->checkOption('#cb-select-'.$postID);
		}

		// Select Edit from the Bulk actions dropdown.
		$I->selectOption('#bulk-action-selector-top', 'Edit');

		// Click Apply button.
		$I->click('#doaction');
	}
}