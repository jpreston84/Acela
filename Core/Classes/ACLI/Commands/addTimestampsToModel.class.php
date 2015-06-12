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

		echo 'Adding created/updated fields to the '.$args[0].' model...';
		
		/**
		 * Get manager.
		 */
		$manager = Core\Model::getInstance($args[0]);

		/**
		 *  Get the table.
		 */
		$schemaTable = Core\Schema::get($manager->databaseTableName);

		/**
		 * Find ID field.
		 */
		foreach($schemaTable->constraints as $schemaConstraint)
		{
			if($schemaConstraint->type === 'primary')
			{
				$idField = $schemaConstraint->fieldNames[0];
				break;
			}
		}
		unset($schemaConstraint);

		/**
		 *  Add additional fields.
		 */
		$schemaTable->dateTime($manager->databaseFieldPrefix.'CreatedOn')->notNullable()->after($idField)->index();
		$schemaTable->bigInt($manager->databaseFieldPrefix.'CreatedBy')->notNullable()->after($manager->databaseFieldPrefix.'CreatedOn')->index();
		$schemaTable->dateTime($manager->databaseFieldPrefix.'ModifiedOn')->notNullable()->after($manager->databaseFieldPrefix.'CreatedBy')->index();
		$schemaTable->bigInt($manager->databaseFieldPrefix.'ModifiedBy')->notNullable()->after($manager->databaseFieldPrefix.'ModifiedOn')->index();
		
		/**
		 *  Save table changes.
		 */
		$schemaTable->save();
		
		echo 'complete.'.PHP_EOL;

		return;
	}
}

