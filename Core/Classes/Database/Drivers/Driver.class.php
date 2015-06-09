<?php
/**
 * A template for database drivers.
 */

namespace Acela\Core\Database\Drivers;

use \Acela\Core\Database;

/**
 * Template for database drivers.
 */
abstract class Driver
{
	/**
	 *  @var Database\Engine The database engine instance that's using this driver.
	 */
	public $engine;

	/**
	 * Run a query to get data about a particular table and its fields from the
	 * database.
	 * 
	 * @param string $tableName The name of the table to get information about.
	 * @return array An array of data about the table and its fields.
	 */
	abstract public function getTableInfo($tableName);
	
	/**
	 * Get an appropriate schema object for this driver.
	 * 
	 * @return Schema The appropriate schema object for this driver.
	 */
	abstract public function schema();
}