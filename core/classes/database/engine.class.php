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
	 * @param DatabaseEngineConfiguration $config A database configuration.
	 */
	public function __construct(Configuration $config)
	{
		/**
		 * Load the driver for this engine instance and pass it appropriate
		 * configuration data.
		 */
		$tmpDriverClass = 'Driver\\'.$config->driver.'\Driver';
		$this->driver = new $tmpDriverClass($config);
	}
}
