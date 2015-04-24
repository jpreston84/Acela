<?php
/**
 *  The database handler.
 */

namespace Acela\Core\Database;

/**
 *  Database handler class.
 */
class DatabaseEngine
{
	private $conn = null;
	
	/**
	 *  Instantiate the database handler and connect to the specified source.
	 *  
	 *  @param string $configName A database configuration name.
	 *  @see config/database.config.php
	 */
	public function __construct($configName)
	{
	}
}
