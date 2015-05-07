<?php
/**
 * ACLI Command - Add timestamps to model.
 */

namespace Acela\Application;

use \Acela\Core as Core;

/**
 * Check for necessary parameters.
 */
if(empty($argv[2]))
{
	echo 'No model name was provided. Unable to continue.'."\n";
	echo 'Usage: ./acli addTimestampsToModel modelName'."\n";
	die();
}

/**
 * Get manager.
 */
$manager = Core\Model::getInstance($argv[2]);

/**
 * Get table name.
 */
$tableName = $manager->databaseTableName;

/**
 * Get field names.
 */
$databaseFields = $manager->databaseFieldInfo;

/**
 * Find ID field.
 */
foreach($databaseFields as $field)
{
	if($field['primary'])
	{
		$idField = $field['name'];
		break;
	}
}

/**
 * Build strings for additional fields.
 */
$addStrings = [];
$addStrings[] = 'ADD `'.$manager->databaseFieldPrefix.'CreatedOn` DATETIME NOT NULL AFTER `'.$idField.'`';
$addStrings[] = 'ADD `'.$manager->databaseFieldPrefix.'CreatedBy` BIGINT NOT NULL AFTER `'.$manager->databaseFieldPrefix.'CreatedOn`';
$addStrings[] = 'ADD `'.$manager->databaseFieldPrefix.'ModifiedOn` DATETIME NOT NULL AFTER `'.$manager->databaseFieldPrefix.'CreatedBy`';
$addStrings[] = 'ADD `'.$manager->databaseFieldPrefix.'ModifiedBy` BIGINT NOT NULL AFTER `'.$manager->databaseFieldPrefix.'ModifiedOn`';

/**
 * Create the query.
 */
$query = '
	ALTER TABLE
		`'.$tableName.'`
	'.implode(', ', $addStrings).'
	;
';
$GLOBALS['core']->db->rawQuery($query);

echo 'Complete.'."\n";
