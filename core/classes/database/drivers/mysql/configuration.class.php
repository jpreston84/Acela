<?php
/**
 * A class for storing configurations for the MySQL driver.
 */

namespace Acela\Core\Database\Drivers\MySQL;

use Acela\Core as Core;

/**
 * A configuration for the MySQL database driver.
 * @see DatabaseEngine DatabaseEngine
 */
class Configuration extends Core\Database\Configuration
{
	/**
	 * @var string $driver The name of the driver.
	 */
	public $driver = 'MySQL';
	
	/**
	 * @var string $host The location of the host server.
	 */
	public $host;
	
	/**
	 * @var string $username MySQL user name.
	 */
	public $username;
	
	/**
	 * @var string $password MySQL user password.
	 */
	public $password;
	
	/**
	 * @var string $database MySQL database name.
	 */
	public $database;
}
