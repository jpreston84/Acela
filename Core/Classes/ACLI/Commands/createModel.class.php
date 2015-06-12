<?php
/**
 * ACLI Command - Create new database model.
 */

namespace Acela\Core\ACLI\Commands;

use \Acela\Core as Core;

class createModel extends Core\ACLI\Command
{
	/**
	 * Get usage for this command.
	 * @return string The usage information for this command.
	 */
	public static function getUsage()
	{
		$usage = [];
		$usage['command'] = 'createModel [modelName]';
		$usage['description'] = 'Create the basic database table for the specified model name.';
		
		return $usage;
	}
	
	/**
	 * Display usage for this command.
	 */
	public static function displayUsage()
	{
		echo 'Usage: ./acli createModel [modelName]'.PHP_EOL;
		echo 'Create the basic database table for the specified model name.'.PHP_EOL;
	}
	
	
	
	/**
	 * Run the command, creating the database table for the specified model.
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

		echo 'Creating table for the '.$args[0].' model...';
		
		/**
		 * Get manager.
		 */
		$manager = Core\Model::getInstance($args[0]);

		/**
		 *  Get table name.
		 */
		$tableName = $manager->databaseTableName;
		
		/**
		 *  Check for existing backup table.
		 */
		if(Core\Database\Engine::tableExists($tableName))
		{
			do
			{
				$input = readline('Model table "'.$tableName.'" already exists. Do you want to delete and re-create it? (yes/no): ');
				if($input === 'yes')
				{
					echo 'Deleting table "'.$tableName.'"...';
					Core\Schema::get($tableName)->delete()->save();
					echo 'done.'.PHP_EOL;
					break;
				}
				elseif($input === 'no')
				{
					echo 'Aborting operation.'.PHP_EOL;
					return false;
				}
				else
				{
					echo 'Invalid input. Please enter only "yes" or "no".'.PHP_EOL;
				}
			} while(true);
		}
		
		/**
		 *  Create the table.
		 */
		$schemaTable = Core\Schema::createTable($tableName);

		/**
		 *  Add fields.
		 */
		$schemaTable->bigInt($manager->databaseFieldPrefix.'Id')->notNullable()->primaryKey()->autoIncrement();
		$schemaTable->dateTime($manager->databaseFieldPrefix.'CreatedOn')->notNullable()->index();
		$schemaTable->bigInt($manager->databaseFieldPrefix.'CreatedBy')->notNullable()->index();
		$schemaTable->dateTime($manager->databaseFieldPrefix.'ModifiedOn')->notNullable()->index();
		$schemaTable->bigInt($manager->databaseFieldPrefix.'ModifiedBy')->notNullable()->index();
		$schemaTable->bool($manager->databaseFieldPrefix.'Disabled')->notNullable()->index();
		$schemaTable->bool($manager->databaseFieldPrefix.'Deleted')->notNullable()->index();
		
		/**
		 *  Save table changes.
		 */
		$schemaTable->save();
		
		echo 'complete.'.PHP_EOL;

		return;
	}
}

