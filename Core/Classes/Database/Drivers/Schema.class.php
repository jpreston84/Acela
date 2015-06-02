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
class Schema extends Core\Singleton
{
	/**
	 * @var Driver $driver A database driver instance.
	 */
	public $driver;
	
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
			return false;
		}
		
		/**
		 * Generate a new schema table instance.
		 */
		$schemaTable = $this->getTableInstance();
		
		/**
		 * Load the SchemaTable.
		 */
		$schemaTable->loadTable($tableName);
		
		return $schemaTable;
	}
	
	/**
	 * Get a blank instance of the SchemaTable for the current driver.
	 * 
	 * @return SchemaTable A blank SchemaTable.
	 */
	protected function getTableInstance()
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
}
