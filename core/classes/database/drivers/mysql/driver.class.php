<?php
/**
 *  Database driver for connecting to a MySQL database.
 */

namespace Acela\Core\Database\Drivers\MySQL;

use \Acela\Core\Database as Database;

/**
 *  Database driver class for MySQL.
 */
class Driver extends Database\DriverTemplate
{
	/**
	 * @var Configuration $config The configuration for this driver. 
	 */
	public $config;
	
	/**
	 * @var PDO $pdo The PDO handle for this driver.
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
}
