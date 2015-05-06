<?php
/**
 * A template for database drivers.
 */

namespace Acela\Core\Database\Drivers;

/**
 * Template for database drivers.
 */
abstract class Driver
{
	/**
	 * Run a query to get data about a particular table and its fields from the
	 * database.
	 * 
	 * @param string $tableName The name of the table to get information about.
	 * @return array An array of data about the table and its fields.
	 */
	abstract public function getTableInfo($tableName);
}