<?php
/**
 *  Database driver for connecting to a MySQL database.
 */

namespace Acela\Core\Database\Drivers\MySQL;

use \Acela\Core\Database as Database;

/**
 *  Database driver class for MySQL.
 */
class Driver extends Database\Drivers\Driver
{
	/**
	 * @var Configuration $config The configuration for this driver. 
	 */
	public $config;
	
	/**
	 * @var \PDO $pdo The PDO handle for this driver.
	 */
	public $pdo;
	
	/**
	 * Instantiate the MySQL driver and connect to the database.
	 * 
	 * @param Configuration $config MySQL configuration options.
	 */
	public function __construct(Configuration $config)
	{
		/**
		 * Save configuration details to the class.
		 */
		$this->config = $config;
		
		/**
		 * Create a persistent database connection to the MySQL server.
		 */
		$this->pdo = new \PDO(
			'mysql:host='.$this->config->host.';dbname='.$this->config->database,
			$this->config->username,
			$this->config->password,
			[
				\PDO::ATTR_PERSISTENT => true,
			]
		);
	}
	
	/**
	 * Run a query just as it is and return the resource handle for the result
	 * set. No extra processing.
	 * 
	 * @param string $query An SQL query you wish to run.
	 * @return ResultSet A result set.
	 */
	public function rawQuery($query)
	{
		$stmt = $this->pdo->query($query); // Run the query and retrieve the result handle.

		$resultSet = new ResultSet($stmt);
		$resultSet->driver = $this;

		return $resultSet; // Return the result set.
	}
	
	/**
	 * Create a new query to run against the database.
	 * 
	 * @return Query A new database query object.
	 */
	public function query()
	{
		return new Query;
	}
	
	/**
	 * Make a string safe for MySQL database use.
	 * 
	 * @param string $string A string to be sanitized.
	 * @return string The sanitized string.
	 */
	public function safeString($string)
	{
		$string = $this->pdo->quote($string);
		return $string;
	}
}
