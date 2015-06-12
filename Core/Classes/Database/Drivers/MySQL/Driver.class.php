<?php
/**
 *  Database driver for connecting to a MySQL database.
 */

namespace Acela\Core\Database\Drivers\MySQL;

use \Acela\Core as Core;
use \Acela\Core\Error as Error;
use \Acela\Core\Database as Database;

/**
 *  Database driver class for MySQL.
 */
class Driver extends Database\Drivers\Driver
{
	/**
	 * @var Configuration $config The configuration for this driver. 
	 */
	public $config;
	
	/**
	 * @var \PDO $pdo The PDO handle for this driver.
	 */
	public $pdo;
	
	/**
	 * Instantiate the MySQL driver and connect to the database.
	 * 
	 * @param Configuration $config MySQL configuration options.
	 */
	public function __construct(Configuration $config)
	{
		/**
		 * Save configuration details to the class.
		 */
		$this->config = $config;
		
		/**
		 * Create a persistent database connection to the MySQL server.
		 */
		$this->pdo = new \PDO(
			'mysql:host='.$this->config->host.';dbname='.$this->config->database,
			$this->config->username,
			$this->config->password,
			[
				\PDO::ATTR_PERSISTENT => true,
				\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
			]
		);
	}
	
	/**
	 * Run a query just as it is and return the resource handle for the result
	 * set. No extra processing.
	 * 
	 * @param string $query An SQL query you wish to run.
	 * @param ResultSet|null $resultSet An existing result set to reset (optional).
	 * @return ResultSet A result set.
	 */
	public function rawQuery($query, $resultSet = null)
	{
		$stmt = $this->pdo->query($query); // Run the query and retrieve the result handle.

		/**
		 * Error handling.
		 */
		if($stmt === false)
		{
			Error::critical(
				'There was a query error.',
				null,
				[
					'query' => $query,
					'pdoErrorInfo' => $this->pdo->errorInfo(),
				]
			);
		}
		
		/**
		 * If no result set was provided, create a new one. Otherwise, update the
		 * existing one.
		 */
		if(is_null($resultSet))
		{
			$resultSet = new ResultSet($stmt);
			$resultSet->driver = $this;
			$resultSet->queryData = [$query];
		}
		else // If an existing result set was provided...
		{
			$resultSet->stmt = $stmt; // Pass the new PDO statement variable to it.
		}

		return $resultSet; // Return the result set.
	}
	
	/**
	 * Get the ID of the last row inserted into the database.
	 * 
	 * @return int The ID of the last row inserted into the database.
	 */
	public function getLastInsertId()
	{
		return $this->pdo->lastInsertId();
	}
	
	/**
	 * Determine whether the specified table name exists or not.
	 * 
	 * @param string $tableName The name of the table to check.
	 * @return bool Does the table exist or not?
	 */
	public function tableExists($tableName)
	{
		$query = 'SHOW TABLES LIKE '.$this->safeString($tableName).';';
		$tableExists = $this->pdo->query($query)->rowCount() > 0;
		return $tableExists;
	}
	
	/**
	 * Run a query to get data about a particular table and its fields from the
	 * database.
	 * 
	 * @param string $tableName The name of the table to get information about.
	 * @return array An array of data about the table and its fields.
	 */
	public function getTableInfo($tableName)
	{
		/**
		 * Set up the array to hold table data.
		 */
		$tableInfo = [];
		
		/**
		 * Run a query to get field information.
		 */
		$query = '
			SELECT
				*
			FROM
				`information_schema`.`COLUMNS`
			WHERE
				`TABLE_SCHEMA` = '.$this->pdo->quote($this->config->database).'
				AND `TABLE_NAME` = '.$this->pdo->quote($tableName).'
			;
		';
		$resultSet = $this->rawQuery($query);
		
		/**
		 * Store the field results.
		 */
		$tableInfo['fields'] = [];
		foreach($resultSet as $num => $result)
		{
			unset($dataType);
			unset($byteLength);
			unset($signed);
			
			/**
			 * Data types.
			 */
			if($result['COLUMN_TYPE'] === 'tinyint(1)')
			{
				$dataType = 'boolean';
			}
			elseif($result['DATA_TYPE'] === 'bigint')
			{
				$dataType = 'int';
				$byteLength = 8;
				$signed = ( stristr($result['COLUMN_TYPE'], 'unsigned') ? false : true );
			}
			elseif($result['DATA_TYPE'] === 'int')
			{
				$dataType = 'int';
				$byteLength = 4;
				$signed = ( stristr($result['COLUMN_TYPE'], 'unsigned') ? false : true );
			}
			elseif($result['DATA_TYPE'] === 'mediumint')
			{
				$dataType = 'int';
				$byteLength = 3;
				$signed = ( stristr($result['COLUMN_TYPE'], 'unsigned') ? false : true );
			}
			elseif($result['DATA_TYPE'] === 'smallint')
			{
				$dataType = 'int';
				$byteLength = 2;
				$signed = ( stristr($result['COLUMN_TYPE'], 'unsigned') ? false : true );
			}
			elseif($result['DATA_TYPE'] === 'tinyint')
			{
				$dataType = 'int';
				$byteLength = 1;
				$signed = ( stristr($result['COLUMN_TYPE'], 'unsigned') ? false : true );
			}
			elseif($result['DATA_TYPE'] === 'varchar')
			{
				$dataType = 'text';
				$byteLength = $result['CHARACTER_MAXIMUM_LENGTH'];
			}
			elseif($result['DATA_TYPE'] === 'tinytext')
			{
				$dataType = 'text';
				$byteLength = 2^8 - 1; // Up to 255 chars.
			}
			elseif($result['DATA_TYPE'] === 'text')
			{
				$dataType = 'text';
				$byteLength = 2^16 - 1; // Up to 65,535 chars.
			}
			elseif($result['DATA_TYPE'] === 'mediumtext')
			{
				$dataType = 'text';
				$byteLength = 2^24 - 1; // Up to 16,777,215 chars.
			}
			elseif($result['DATA_TYPE'] === 'longtext')
			{
				$dataType = 'text';
				$byteLength = 2^32 - 1; // Up to 4,294,967,296 chars.
			}
			elseif($result['DATA_TYPE'] === 'datetime')
			{
				$dataType = 'datetime';
			}
			
			
			/**
			 * Convert bool/null defaults to PHP equivalents.
			 */
			if($result['COLUMN_DEFAULT'] === 'NULL')
			{
				$defaultValue = null;
			}
			elseif($result['COLUMN_DEFAULT'] === 'TRUE')
			{
				$defaultValue = true;
			}
			elseif($result['COLUMN_DEFAULT'] === 'FALSE')
			{
				$defaultValue = false;
			}
			else
			{
				$defaultValue = $result['COLUMN_DEFAULT'];
			}
			
			/**
			 * Is column nullable values.
			 */
			if($result['IS_NULLABLE'] == 'YES')
			{
				$isNullable = true;
			}
			else
			{
				$isNullable = false;
			}
			
			/**
			 * Check for auto increment.
			 */
			$autoIncrement = false;
			if(stristr($result['EXTRA'], 'auto_increment'))
			{
				$autoIncrement = true;
			}
			
			$tableInfo['fields'][$result['COLUMN_NAME']] = [
				'name' => $result['COLUMN_NAME'],
				'type' => $dataType,
				'length' => ( !empty($byteLength) ? $byteLength : null ) ,
				'signed' => ( !empty($signed) ? true : false ),
				'default' => $defaultValue,
				'nullable' => $isNullable,
				'autoIncrement' => $autoIncrement,
				'positionFirst' => ( $num == 0 ? true : false ),
				'positionAfter' => ( $num == 0 ? false : $previousFieldName ),
			];
			
			/**
			 *  Set previous field name so we can use it for positionAfter.
			 */
			$previousFieldName = $result['COLUMN_NAME'];
		}
		
		/**
		 * Run a query to get constraint information.
		 */
		$query = '
			SELECT
				*
			FROM
				`information_schema`.`STATISTICS`
			WHERE
				`TABLE_SCHEMA` = '.$this->pdo->quote($this->config->database).'
				AND `TABLE_NAME` = '.$this->pdo->quote($tableName).'
			;
		';
		$resultSet = $this->rawQuery($query);

		/**
		 * Store the constraint results.
		 */
		$tableInfo['constraints'] = [];
		foreach($resultSet as $num => $result)
		{
			/**
			 *  Throw an error for unhandled index types.
			 */
			if($result['INDEX_TYPE'] !== 'BTREE')
			{
				Core\Error::critical('Unable to load table "'.$tableName.'" because it has one or more indexes which are not BTREE indexes.');
			}
			
			/**
			 *  Check whether this is just an additional field for an existing index.
			 */
			if(!empty($tableInfo['constraints'][$result['INDEX_NAME']]))
			{
				$tableInfo['constraints'][$result['INDEX_NAME']][] = $result['COLUMN_NAME'];
				continue;
			}
		
			/**
			 *  Constraint types.
			 */
			if($result['INDEX_NAME'] === 'PRIMARY') // Primary Keys...
			{
				$constraintType = 'primary';
				$unique = true;
				$fieldNames = [ $result['COLUMN_NAME'] ];
			}
			else
			{
				$constraintType = 'index';
				$unique = !((bool) $result['NON_UNIQUE']);
				$fieldNames = [ $result['COLUMN_NAME'] ];
			}
			
			$tableInfo['constraints'][$result['INDEX_NAME']] = [
				'name' => $result['INDEX_NAME'],
				'type' => $constraintType,
				'unique' => $unique,
				'fieldNames' => $fieldNames,
			];
		}

		
		return $tableInfo;
	}
	
	/**
	 * Create a new query to run against the database.
	 * 
	 * @return Query A new database query object.
	 */
	public function query()
	{
		return new Query;
	}
	
	/**
	 * Make a string safe for MySQL database use.
	 * 
	 * @param string $string A string to be sanitized.
	 * @return string The sanitized string.
	 */
	public function safeString($string)
	{
		$string = $this->pdo->quote($string);
		return $string;
	}
	
	public function schema()
	{
		$schema = Schema::getInstance();
		$schema->driver = $this;
		return $schema;
	}
}
