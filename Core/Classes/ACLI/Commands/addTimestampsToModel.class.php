<?php
/**
 * ACLI Command - Add timestamps to model.
 */

namespace Acela\Core\ACLI\Commands;

use \Acela\Core as Core;

class addTimestampsToModel extends Core\ACLI\Command
{
	/**
	 * Get usage for this command.
	 * @return string The usage information for this command.
	 */
	public static function getUsage()
	{
		$usage = [];
		$usage['command'] = 'addTimestampsToModel [modelName]';
		$usage['description'] = 'Add created/updated fields to the database table for the specified model name.';
		
		return $usage;
	}
	
	/**
	 * Display usage for this command.
	 */
	public static function displayUsage()
	{
		echo 'Usage: ./acli addTimestampsToModel [modelName]'.PHP_EOL;
		echo 'Add created/updated fields to the database table for the specified model name.'.PHP_EOL;
	}
	
	
	
	/**
	 * Run the command, adding timestamps to the specified model.
	 * 
	 * @param array $args An array of arguments passed to the command.
	 */
	public function run($args)
	{
		/**
		 * Check for necessary parameters.
		 */
		if(empty($args[0]))
		{
			$this->displayUsage();
			die();
		}

		/**
		 * Get manager.
		 */
		$manager = Core\Model::getInstance($argv[0]);

		/**
		 * Get table name.
		 */
		$tableName = $manager->databaseTableName;

		/**
		 * Get field names.
		 */
		$databaseFields = $manager->databaseTableInfo['fields'];

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
		
		return;
	}
}

