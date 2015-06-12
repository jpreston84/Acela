<?php
/**
 * ACLI Command - Add a pivot table linking two models.
 */

namespace Acela\Core\ACLI\Commands;

use \Acela\Core as Core;

/**
 * ACLI Command - Add a pivot table linking two models.
 */
class addPivotTable extends Core\ACLI\Command
{
	/**
	 * Get usage for this command.
	 * @return string The usage information for this command.
	 */
	public static function getUsage()
	{
		$usage = [];
		$usage['command'] = 'addPivotTable [modelName] [anotherModelName]';
		$usage['description'] = 'Add a pivot table between the specified models.';
		
		return $usage;
	}
	
	/**
	 * Display usage for this command.
	 */
	public static function displayUsage()
	{
		echo 'Usage: ./acli addPivotTable [modelName] [anotherModelName]'.PHP_EOL;
		echo 'Add a pivot table between the specified models.'.PHP_EOL;
	}
	
	/**
	 * Run the command, adding a pivot table between the specified models.
	 * 
	 * @param array $args An array of arguments passed to the command.
	 */
	public function run($args)
	{
		/**
		 * Check for necessary parameters.
		 */
		if(empty($args[1]))
		{
			$this->displayUsage();
			die();
		}

		echo 'Adding pivot table for the '.$args[0].' and '.$args[1].' models...'.PHP_EOL;

		/**
		 *  Get pivot manager.
		 */
		$pivotManager = Core\Model::getInstance(Core\Model::getPivotModelName($args[0], $args[1]));
		
		/**
		 * Get managers.
		 */
		$manager1 = Core\Model::getInstance($args[0]);
		$manager2 = Core\Model::getInstance($args[1]);

		/**
		 *  Get ID field names.
		 */
		$idField1 = $manager1->getDatabaseFieldName('id');
		$idField2 = $manager2->getDatabaseFieldName('id');
		
		/**
		 *  Get pivot table name.
		 */
		$pivotTableName = $pivotManager->databaseTableName;
		
		/**
		 *  Get pivot field prefix.
		 */
		$pivotPrefix = $pivotManager->databaseFieldPrefix;

		/**
		 *  Check for existing pivot table.
		 */
		if(Core\Database\Engine::tableExists($pivotTableName))
		{
			do
			{
				$input = readline('Backup table "'.$pivotTableName.'" already exists. Do you want to delete and re-create it? (yes/no): ');
				if($input === 'yes')
				{
					echo 'Deleting table "'.$pivotTableName.'"...';
					Core\Schema::get($pivotTableName)->delete()->save();
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
		 * Create pivot table.
		 */
		echo 'Creating table `'.$pivotTableName.'`...';
		$schemaTable = Core\Schema::createTable($pivotTableName); // Create the table.
		$schemaTable->bigInt($pivotPrefix.'Id')->notNullable()->primaryKey()->autoIncrement();
		$schemaTable->dateTime($pivotPrefix.'CreatedOn')->notNullable()->index();
		$schemaTable->bigInt($pivotPrefix.'CreatedBy')->notNullable()->index();
		$schemaTable->dateTime($pivotPrefix.'ModifiedOn')->notNullable()->index();
		$schemaTable->bigInt($pivotPrefix.'ModifiedBy')->notNullable()->index();
		$schemaTable->bool($pivotPrefix.'Disabled')->notNullable()->index();
		$schemaTable->bool($pivotPrefix.'Deleted')->notNullable()->index();

		/**
		 *  Add ID fields and index them.
		 */
		$schemaTable->bigInt($idField1)->notNullable()->index();
		$schemaTable->bigInt($idField2)->notNullable()->index();
		
		/**
		 *  Save the table.
		 */
		$schemaTable->save();
		echo 'done.'.PHP_EOL;
		
		return;
	}
}

