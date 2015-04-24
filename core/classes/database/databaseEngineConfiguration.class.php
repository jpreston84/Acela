<?php
/**
 * A class for storing configurations for the DatabaseEngine.
 * @see \Acela\Core\Database\DatabaseEngine
 */

namespace Acela\Core\Database;

/**
 * A configuration for the database engine.
 */
abstract class DatabaseEngineConfiguration
{
	/**
	 * The name of this configuration.
	 */
	public $name;
	
	/**
	 * The name of the driver used in this configuration.
	 */
	public $driver;
	
	/**
	 * Is this configuration read-only?
	 */
	public $readOnly = false;
}