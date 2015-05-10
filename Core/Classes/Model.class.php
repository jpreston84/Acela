<?php
/**
 * The main model manager.
 */

namespace Acela\Core;

/**
 * The main model manager.
 */
class Model
{
	/**
	 * Return the singleton manager for the specified model type.
	 * 
	 * @param string $name The model type to get the manager for.
	 * @return Models\Manager A manager for the selected model type.
	 */
	public static function getInstance($name)
	{
		$className = __NAMESPACE__.'\Models\\'.$name.'\Manager';
		if(class_exists($className)) // If files exist for this class...
		{
			$instance = $className::getInstance(); // Get the global instance of the class.
			return $instance;
		}
		else // This class does not exist -- attempt to create a Generic object.
		{
			$instance = Models\Generic\Manager::getInstance($name);
			return $instance;
		}
	}
	
	/**
	 * Find a link field or table between two models.
	 * 
	 * This method should only be called from Core\Models\Model->getLinkedObjects().
	 * 
	 * @param string $model1 A model name.
	 * @param string $model2 A second model name.
	 * @return mixed Returns 1 if $model1 contains the link, -1 if $model2 contains the link, 0 if there is a pivot table, and false of there is no link.
	 */
	public static function findLink($model1, $model2)
	{
		/**
		 * Get managers for the two specified model names.
		 */
		$manager1 = self::getInstance($model1);
		$manager2 = self::getInstance($model2);
		
		/**
		 * Look up ID fields for each model.
		 */
		foreach($manager1->databaseTableInfo['fields'] as $field)
		{
			if($field['primary'])
			{
				$idField1 = $field['objectFieldName'];
			}
		}
		foreach($manager2->databaseTableInfo['fields'] as $field)
		{
			if($field['primary'])
			{
				$idField2 = $field['objectFieldName'];
			}
		}
		
		/**
		 * Look for $idField2 in $model1.
		 */
		if($manager1->getObjectFieldName($manager2->getDatabaseFieldName($idField2) !== false)
		{
			return 1;
		}

		/**
		 * Look for $idField1 in $model2.
		 */
		if($manager2->getObjectFieldName($manager1->getDatabaseFieldName($idField1) !== false)
		{
			return -1;
		}
		
		/**
		 * Look for a pivot table.
		 */
		$pivotTableName = self::getPivotTableName($manager1->databaseTableName, $manager2->databaseTableName);
		if($GLOBALS['core']->db->tableExists($pivotTableName))
		{
			return 0;
		}
		
		/**
		 * No link found, return false.
		 */
		return false;
	}

	/**
	 * Get the name of the pivot model that would link two models.
	 * 
	 * @param string $modelName1 The first model to link.
	 * @param string $modelName2 The second model to link.
	 * @return string The appropriate name for a pivot model.
	 */
	public static function getPivotModelName($modelName1, $modelName2)
	{
		/**
		 * Models need to be in alphabetical order. If they are not, swap orders and try
		 * again.
		 */
		if(strcmp($modelName1, $modelName2) > 0) // If the model names are not in alphabetical order...
		{
			return self::getPivotModelName($modelName2, $modelName1); // Swap the arguments, and re-run the function.
		}
		
		/**
		 * The pivot object name is formed by combining the two model object names
		 * provided.
		 */
		$pivotModelName = $modelName1.$modelName2;

		return $pivotModelName;
	}
	
	/**
	 * Get the name of the pivot table that would link two model tables.
	 * 
	 * @param string $table1 The first table to link.
	 * @param string $table2 The second table to link.
	 * @return string The appropriate name for a pivot table between these two model tables.
	 */
	public static function getPivotTableName($table1, $table2)
	{
		/**
		 * Models need to be in alphabetical order (by table name). If they are
		 * not, swap orders and try again.
		 */
		if(strcmp($table1, $table2) > 0) // The table names are not in alphabetical order.
		{
			return self::getPivotTableName($table2, $table1); // Swap the arguments, and re-run the function.
		}
		
		/**
		 * The pivot table name is formed by singularizing the word form of each of the
		 * two model tables provided. The two singularized forms are then combined, and
		 * re-pluralized. Additionally, the first character of the second model table is
		 * capitalized.
		 */
		$table1 = Core\wordSingularize($table1);
		$table2 = Core\wordSingularize($table2);
		$table2[0] = strtoupper($table2[0]);
		$pivotTableName = Core\wordPluralize($table1.$table2);

		return $pivotTableName;
	}
}
