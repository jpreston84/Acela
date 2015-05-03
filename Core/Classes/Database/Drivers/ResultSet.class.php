<?php
/**
 * A template for database result sets.
 */

namespace Acela\Core\Database\Drivers;

/**
 * Template for database result sets.
 */
abstract class ResultSet implements \Countable, \Iterator
{
	/**
	 * @var Driver $driver A reference to the database driver being used.
	 */
	public $driver;

	/**
	 * @var array $queryData Data needed to recreate the query.
	 */
	public $queryData;
	
	/**
	 * Get the next record in the result set.
	 * 
	 * @return mixed The next record in the result set.
	 */	
	abstract public function get();
	
	/**
	 * Get all remaining records in the result set.
	 * 
	 * @return array An array of all remaining records in the result set.
	 */
	abstract public function getAll();
	
	/**
	 * Close the current ResultSet.
	 */
	abstract public function close();
	
	/**
	 * Reset the ResultSet to its original state.
	 * 
	 * This may involve re-running a database query.
	 */
	abstract public function reset();	
}
