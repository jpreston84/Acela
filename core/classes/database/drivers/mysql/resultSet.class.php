<?php
/**
 * A class for MySQL database result sets.
 */

namespace Acela\Core\Database\Drivers\MySQL;

use \Acela\Core\Database as Database;

/**
 * Class for MySQL database result sets.
 */
class ResultSet extends Database\ResultSet
{
	/**
	 * @var \PDOStatement $res Resource for the result set.
	 */
	private $res;
	
	/**
	 * Instantiate the result set and save the PDO resource.
	 * 
	 * @param \PDOStatement $res The PDO statement result set that will be used.
	 */
	function __construct($res)
	{
		$this->res = $res;
	}
	
	/**
	 * Get one or more rows of data from the record set.
	 * 
	 * @param int $qty The number of rows to retrieve. Default 1.
	 * @return array|false An associative array containing the names of columns as array keys and values of columns as array values.
	 */
	function get($qty = 1)
	{
		return $res->fetch();
	}

	/**
	 * Get all rows of data from the record set.
	 * 
	 * @return array|false An multidimensional array containing all of the records from the data set, stored as associative arrays.
	 */
	function getAll()
	{
		return $res->fetchAll();
	}
}