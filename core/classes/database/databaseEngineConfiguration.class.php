<?php
/**
 * A class for storing configurations for the DatabaseEngine.
 */

namespace Acela\Core\Database;

/**
 * A configuration for the database engine.
 * @see DatabaseEngine DatabaseEngine
 */
abstract class DatabaseEngineConfiguration
{
	/**
	 * @var string $name The name of this configuration.
	 */
	public $name;
	
	/**
	 * @var string $driver The name of the driver used in this configuration.
	 */
	public $driver;
	
	/**
	 * @var string $readOnly Is this configuration read-only?
	 */
	public $readOnly = false;
}