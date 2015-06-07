<?php
/**
 * Template for database schema handlers.
 */

namespace Acela\Core\Database\Drivers;

use \Acela\Core;
use \Acela\Core\Database;

/**
 * Template for database schema handlers.
 */
class Schema extends Core\Singleton implements \Iterator
{
	/**
	 * Use the IterateItems trait.
	 */
	use Core\IterateItems;
	
	/**
	 * @var Driver $driver A database driver instance.
	 */
	public $driver;
	
	/**
	 * @var array $items An array of SchemaTable objects.
	 */
	public $items = [];
	
	/**
	 *  Magic Method - Allow this class to function as a Singleton.
	 */
	public static function __callStatic($name, $arguments)
	{
		/**
		 * Load the appropriate Schema from the global db instance.
		 */
		$schema = Database\Engine::schema();
		
		/**
		 * Call the method.
		 */
		return call_user_func_array([$schema, $name], $arguments);
	}
	
	/**
	 *  Create a new SchemaTable.
	 *  
	 *  @param string $tableName The name of the table to be created.
	 *  @return SchemaTable The completed table.
	 *  
	 *  @details Details
	 */
	protected function createTable($tableName)
	{
		/**
		 *  Check to see if table already exists.
		 */
		if($this->driver->tableExists($tableName))
		{
			Core\Error::critical('Unable to create schema table "'.$tableName.'" because a table with that name already exists in the database.');
		}
		
		/**
		 *  Create a new table object.
		 */
		$schemaTable = $this->getSchemaTableInstance();
		
		/**
		 *  Set the name of the table.
		 */
		$schemaTable->name = $tableName;

		/**
		 *  Add the SchemaTable to the ->items stack.
		 */
		$this->items[] = $schemaTable;
		
		/**
		 *  Return the completed table.
		 */
		return $schemaTable;
	}
	
	/**
	 * Get a particular table from the schema.
	 * 
	 * @param string $tableName The name of the table in the schema to get.
	 * @return SchemaTable A constructed instance of the table.
	 */
	protected function get($tableName)
	{
		/**
		 * Verify that the table exists.
		 */
		if(!$this->driver->tableExists($tableName))
		{
			Core\Error::critical('Unable to load database table "'.$tableName.'", because a table with that name does not exist.');
		}
		
		/**
		 *  Look for the table in the ->items stack.
		 */
		foreach($this->items as $schemaTable) // Look through each table that's already been loaded...
		{
			if($schemaTable->name === $tableName) // If the iterated table matches the name of the table we're looking for...
			{
				return $schemaTable; // Return the iterated table, rather than re-loading the table.
			}
		}
		unset($schemaTable);
		
		/**
		 * Generate a new schema table instance.
		 */
		$schemaTable = $this->getSchemaTableInstance();
		
		/**
		 * Load the SchemaTable.
		 */
		$schemaTable->loadTable($tableName);
		
		/**
		 *  Add the SchemaTable to the ->items stack.
		 */
		$this->items[] = $schemaTable;
		
		return $schemaTable;
	}
	
	/**
	 * Get a blank instance of the SchemaTable for the current driver.
	 * 
	 * @return SchemaTable A blank SchemaTable.
	 */
	protected function getSchemaTableInstance()
	{
		$className = get_called_class();
		$className .= 'Table';
		$schemaTable = new $className;
		
		/**
		 * Attach the schema to the schema table.
		 */
		$schemaTable->schema = $this;
		
		return $schemaTable;
	}
	
	/**
	 *  Delete the specified table.
	 *  
	 *  @param string $tableName The name of the table to delete.
	 *  @return A reference to the current object.
	 */
	protected function deleteTable($tableName)
	{
		/**
		 *  Get the table specified.
		 */
		$schemaTable = $this->get($tableName);
		
		/**
		 *  Delete the table.
		 */
		$schemaTable->delete();
	}
}
