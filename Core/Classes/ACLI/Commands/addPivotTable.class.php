<?php
/**
 * ACLI Command - Add a pivot table linking two models.
 */

namespace Acela\Core\ACLI\Commands;

use \Acela\Core as Core;

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
		 * Get managers.
		 */
		$manager1 = Core\Model::getInstance($args[0]);
		$manager2 = Core\Model::getInstance($args[1]);
		
		/**
		 * Get ID field names.
		 */
		foreach($manager1->databaseTableInfo['fields'] as $field)
		{
			if($field['primary'])
			{
				$idField1 = $field['name'];
				break;
			}
		}
		foreach($manager2->databaseTableInfo['fields'] as $field)
		{
			if($field['primary'])
			{
				$idField2 = $field['name'];
				break;
			}
		}
		
		/**
		 * Compare existing table names.
		 */
		$tableName1 = $manager1->databaseTableName;
		$tableName2 = $manager2->databaseTableName;
		if(strcmp($tableName1, $tableName2) == -1) // If $tableName1 < $tableName2, meaning it's earlier alphabetically...
		{
			$swap = false; // Do not swap the order of the objects.
		}
		else
		{
			$swap = true; // Swap the order of the objects.
		}
		
		/**
		 * Determine table name.
		 */
		if($swap)
		{
			$tableName1[0] = strtoupper($tableName1[0]);
			$pivotTableName = $tableName2.$tableName1;
		}
		else
		{
			$tableName2[0] = strtoupper($tableName2[0]);
			$pivotTableName = $tableName1.$tableName2;
		}
		
		/**
		 * Determine pivot ID field.
		 */
		if($swap)
		{
			$pivotIdField = $manager2->databaseFieldPrefix.$manager1->databaseFieldPrefix.'Id';
		}
		else
		{
			$pivotIdField = $manager1->databaseFieldPrefix.$manager2->databaseFieldPrefix.'Id';
		}
		$pivotIdField[0] = strtolower($pivotIdField[0]);

		/**
		 * Table field strings.
		 */
		$tableFields = [];
		$tableFields[] = '`'.$pivotIdField.'` BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT';
		if($swap)
		{
			$tableFields[] = '`'.$idField2.'` BIGINT NOT NULL';
			$tableFields[] = '`'.$idField1.'` BIGINT NOT NULL';
		}
		else
		{
			$tableFields[] = '`'.$idField1.'` BIGINT NOT NULL';
			$tableFields[] = '`'.$idField2.'` BIGINT NOT NULL';
		}
		$tableFields = '('.implode(', ', $tableFields).')';
		
		
		/**
		 * Create pivot table.
		 */
		echo 'Creating table `'.$pivotTableName.'`...';
		if($swap)
		$query = 'CREATE TABLE `'.$pivotTableName.'` '.$tableFields.';';
		$GLOBALS['core']->db->rawQuery($query);
		echo 'done.'.PHP_EOL;
		
		return;
	}
}

