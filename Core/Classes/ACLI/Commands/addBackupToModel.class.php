<?php
/**
 * ACLI Command - Add backup table to model.
 */

namespace Acela\Core\ACLI\Commands;

use \Acela\Core as Core;

class addBackupToModel extends Core\ACLI\Command
{
	/**
	 * Get usage for this command.
	 * @return string The usage information for this command.
	 */
	public static function getUsage()
	{
		$usage = [];
		$usage['command'] = 'addBackupToModel [modelName]';
		$usage['description'] = 'Add a backup version table for the specified model.';
		
		return $usage;
	}
	
	/**
	 * Display usage for this command.
	 */
	public static function displayUsage()
	{
		echo 'Usage: ./acli addBackupToModel [modelName]'.PHP_EOL;
		echo 'Add backup version table for the specified model.'.PHP_EOL;
	}
	
	
	
	/**
	 * Run the command, adding a backup table to the specified model.
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

		echo 'Adding backup table for the '.$args[0].' model...'.PHP_EOL;
		
		/**
		 * Get manager.
		 */
		$manager = Core\Model::getInstance($args[0]);

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
		 * Create backup table.
		 */
		echo 'Copying table `'.$tableName.'` to `'.$tableName.'Versions`...';
		$query = 'CREATE TABLE `'.$tableName.'Versions` LIKE `'.$tableName.'`;';
		$GLOBALS['core']->db->rawQuery($query);
		echo 'done.'.PHP_EOL;

		/**
		 * Remove auto increment.
		 */
		echo 'Removing auto-increment on field `'.$idField.'` in table `'.$tableName.'Versions`...';
		$query = 'ALTER TABLE `'.$tableName.'Versions` MODIFY `'.$idField.'` BIGINT NOT NULL;';
		$GLOBALS['core']->db->rawQuery($query);
		echo 'done.'.PHP_EOL;

		/**
		 * Remove auto increment.
		 */
		echo 'Removing primary key on table `'.$tableName.'Versions`...';
		$query = 'ALTER TABLE `'.$tableName.'Versions` DROP PRIMARY KEY;';
		$GLOBALS['core']->db->rawQuery($query);
		echo 'done.'.PHP_EOL;

		/**
		 * Remove auto increment.
		 */
		echo 'Adding field `versionId` on table `'.$tableName.'Versions`...';
		$query = 'ALTER TABLE `'.$tableName.'Versions` ADD `versionId` BIGINT PRIMARY KEY AUTO_INCREMENT FIRST;';
		$GLOBALS['core']->db->rawQuery($query);
		echo 'done.'.PHP_EOL;		

		/**
		 * All done.
		 */
		echo 'Complete.'.PHP_EOL;
		
		return;
	}
}

