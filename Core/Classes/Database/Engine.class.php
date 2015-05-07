<?php
/**
 * The database handler.
 */

namespace Acela\Core\Database;

/**
 * Database handler class.
 */
class Engine
{
	/**
	 * @var DriverTemplate $driver An instance of the database driver being used by this database engine instance.
	 */
	public $driver;
	
	/**
	 * Instantiate the database handler and connect to the specified source.
	 * @param Drivers\Configuration $config A database configuration.
	 */
	public function __construct(Drivers\Configuration $config)
	{
		/**
		 * Load the driver for this engine instance and pass it appropriate
		 * configuration data.
		 */
		$tmpDriverClass = __NAMESPACE__.'\Drivers\\'.$config->driver.'\Driver';
		$this->driver = new $tmpDriverClass($config);
	}
	
	/**
	 * Run a query directly against the database driver, and return the result.
	 * 
	 * @param mixed $data The input for the database driver's ->rawQuery(), which is usually a string.
	 * @return mixed The data returned from the database driver. This is usually an array.
	 */
	public function rawQuery($data)
	{
		return $this->driver->rawQuery($data);
	}
	
	/**
	 * Get the ID for the last row inserted into the database.
	 * 
	 * @return mixed The ID of the last row inserted into the database.
	 */
	public function getLastInsertId()
	{
		return $this->driver->getLastInsertId();
	}
	
	/**
	 * Run a query to get data about a particular table and its fields from the
	 * database.
	 * 
	 * This is a passthrough to ->driver->getTableInfo().
	 * 
	 * @param string $tableName The name of the table to get information about.
	 * @return array An array of data about the table and its fields.
	 */
	public function getTableInfo($tableName)
	{
		return $this->driver->getTableInfo($tableName);
	}
	
	/**
	 * Generate a new query object for the selected database driver.
	 * 
	 * @return Driver\Query A database query object.
	 */
	public function query()
	{
		$tmpQueryClass = __NAMESPACE__.'\Drivers\\'.$this->driver->config->driver.'\Query'; // Determine the full path of the appropriate Query class.
		$query = new $tmpQueryClass(); // Instantiate the Query object.

		$query->driver = $this->driver; // Store a reference to the instantiated driver in the query object.
		
		return $query;		
	}
}
