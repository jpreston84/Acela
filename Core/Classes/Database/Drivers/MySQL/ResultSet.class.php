<?php
/**
 * A class for MySQL database result sets.
 */

namespace Acela\Core\Database\Drivers\MySQL;

use \Acela\Core\Database as Database;

/**
 * Class for MySQL database result sets.
 */
class ResultSet extends Database\Drivers\ResultSet implements Countable, Iterator
{
	/**
	 * @var \PDOStatement $stmt Resource for the result set.
	 */
	private $stmt;
	
	/**
	 * @var array $currentRecord The current record from the data set (for Iterator interface).
	 */
	private $currentRecord = null;

	/**
	 * @var array $currentRecordKey A key for the current record from the data set (for Iterator interface).
	 */
	private $currentRecordKey;
	
	/**
	 * Instantiate the result set and save the PDO resource.
	 * 
	 * @param \PDOStatement $stmt The PDO statement result set that will be used.
	 */
	public function __construct($stmt)
	{
		$this->stmt = $stmt;
		$this->stmt->setFetchMode(PDO::FETCH_ASSOC); // Set the result handler to return associative arrays.
	}
	
	/**
	 * Magic method to get values like num, etc.
	 * 
	 * @param string $name The name of the property to check.
	 * @return mixed The value of the dynamic property.
	 */
	public function __get($name)
	{
		if($name === 'num')
		{
			return $this->count();
		}
	}
	
	/**
	 * Iterator Interface - Return the current record from the data set.
	 * 
	 * @return array The current record from the data set.
	 */
	public function current()
	{
		return $this->currentRecord;
	}

	/**
	 * Iterator Interface - Return the key of the current record from the data set.
	 * 
	 * @return array The key of the current record of the data set.
	 */
	public function key()
	{
		return $this->currentRecordKey;
	}
	
	/**
	 * Iterator Interface - Move to the next element.
	 */
	public function next()
	{
		$this->get();
	}
	
	/**
	 * Iterator Interface - Rewind to the first element.
	 */
	public function rewind()
	{
		$this->stmt->rewind();
	}
	
	/**
	 * Iterator Interface - Determine if the current position is valid.
	 * 
	 * @return bool Indicates whether the current position is valid or not.
	 */
	public function valid()
	{
		if(!empty($this->currentRecord))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Countable Interface - Count the number of records in this result set.
	 * 
	 * @return int The number of records in the result set.
	 */
	public function count()
	{
		return $this->stmt->rowCount();
	}
	
	/**
	 * Get one or more rows of data from the record set.
	 * 
	 * @param int $qty The number of rows to retrieve. Default 1.
	 * @return array|false An associative array containing the names of columns as array keys and values of columns as array values.
	 */
	public function get($qty = 1)
	{
		/**
		 * Update the ->currentRecordKey.
		 */
		if(is_null($this->currentRecordKey))
		{
			$this->currentRecordKey = 0;
		}
		else
		{
			$this->currentRecordKey++;
		}
		
		/**
		 * Get the next record and store it.
		 */
		$this->currentRecord = $this->stmt->fetch();
				
		return $this->currentRecord;
	}

	/**
	 * Get all rows of data from the record set.
	 * 
	 * @return array|false An multidimensional array containing all of the records from the data set, stored as associative arrays.
	 */
	public function getAll()
	{
		return $this->stmt->fetchAll();
	}
}