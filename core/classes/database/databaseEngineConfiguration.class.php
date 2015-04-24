<?php
/**
 * A class for storing configurations for the DatabaseEngine.
 */

namespace Acela\Core\Database;

/**
 * A configuration for the database engine.
 * @see DatabaseEngine
 * @property string $name The name of this configuration.
 * @property string $driver The name of the driver used in this configuration.
 * @property bool $readOnly Is this configuration read-only?
 */
abstract class DatabaseEngineConfiguration
{
	public $name;
	public $driver;
	public $readOnly = false;
}