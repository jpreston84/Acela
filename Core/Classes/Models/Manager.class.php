<?php
/**
 *  The data model manager template.
 */

namespace Acela\Core\Models;

use \Acela\Core as Core;

/**
 *  The data model manager template.
 */
abstract class Manager
{
	/**
	 * @var string $modelName The name of the model.
	 */
	public $modelName;
	
	/**
	 * @var string $databaseTableName The name of the database table used by this model.
	 */
	public $databaseTableName;
	
	/**
	 * @var string $databaseFieldPrefix The database field prefix for the fields in the database table.
	 */
	public $databaseFieldPrefix;
	
	/**
	 * @var string $databaseTableInfo Information about each object field from the database table.
	 */
	public $databaseTableInfo;
	
	/**
	 * Return the singleton instance of this manager.
	 * 
	 * It is important to note that this is not the only means of creating an
	 * instance of the manager. However, it is recommended that the singleton
	 * instance be used whenever possible.
	 * 
	 * @return Manager A singleton instance of this manager type.
	 */
	public static function getInstance()
	{
		static $instance;
		
		/**
		 * Create the singleton if it hasn't been created yet.
		 */
		if(is_null($instance)) // If the singleton hasn't been created yet...
		{
			$instance = new static(); // Create a new instance of the manager type that was called (uses late static bindings).
		}
		
		return $instance;
	}
	
	/**
	 * Constructor -- Creates an instance of this manager.
	 */
	public function __construct()
	{
		/**
		 * Set the model name.
		 */
		$this->setModelName();
		
		/**
		 * Set database table name.
		 */
		$this->setDatabaseTableName();

		/**
		 * Set database field prefix.
		 */
		$this->setDatabaseFieldPrefix();
		
		/**
		 * Load field information.
		 */
		$this->loadDatabaseTableInfo();
	}
	
	/**
	 * Determine the model name.
	 */
	protected function setModelName()
	{
		/**
		 * Get the class name.
		 */
		$className = get_called_class();
		
		/**
		 * Break the class name into components.
		 */
		$classNameParts = explode('\\', $className);
		
		/**
		 * Get the model name. Since model classes take the form
		 * \Acela\Core\Models\User\Manager
		 * we will get the penultimate path component.
		 */
		$modelName = $classNameParts[count($classNameParts) - 2];
		
		/**
		 * Set the model name in this class.
		 */
		$this->modelName = $modelName;
	}
	
	/**
	 * Set the database table name.
	 */
	protected function setDatabaseTableName()
	{
		$this->databaseTableName = Core\wordPluralize(strtolower($this->modelName));
	}

	/**
	 * Set the database field prefix.
	 */
	protected function setDatabaseFieldPrefix()
	{
		$this->databaseFieldPrefix = strtolower($this->modelName);
	}
	
	/**
	 * Get one or more records matching the provided parameters.
	 * 
	 * @param array $params An array of parameters to use in selecting records.
	 * @param int $qty The number of records to retrieve.
	 * @return ResultSet A set of results.
	 */
	public function get($params, $qty = 1)
	{
		$query = $GLOBALS['core']->db->query();
		$query->table($this->databaseTableName, 't1');
		$query->quantity($qty);
		$results = $query->run();
		
		$className = __NAMESPACE__.'\\'.$this->modelName.'\ResultSet';
		$resultSet = new $className;
		$resultSet->manager = $this;
		$resultSet->databaseResultSet = $results;
		$resultSet->loadResultSet();
		
		return $resultSet;
	}
	
	/**
	 * Load information about the table from the database.
	 */
	public function loadDatabaseTableInfo()
	{
		$this->databaseTableInfo = $GLOBALS['core']->db->getTableInfo($this->databaseTableName);

		/**
		 * Process the entries.
		 */
		foreach($this->databaseTableInfo['fields'] as &$field)
		{
			$field['objectFieldName'] = $this->convertToObjectFieldName($field['name']);
		}
		unset($field); // Unset the reference.
	}

	/**
	 * Convert a database field name to an appropriate object field name.
	 * 
	 * This function should never be called except by ->loadDatabaseTableInfo(). For
	 * all other purposes, use ->getObjectFieldName() instead.
	 * 
	 * @param string $databaseFieldName The name of the database field to map to an object field.
	 * @return string The name of the object field.
	 */	
	protected function convertToObjectFieldName($databaseFieldName)
	{
		/**
		 * If database field name begins with the database field prefix, remove the
		 * prefix.
		 */
		if(substr($databaseFieldName, 0, strlen($this->databaseFieldPrefix)) === $this->databaseFieldPrefix)
		{
			$objectFieldName = substr($databaseFieldName, strlen($this->databaseFieldPrefix));
			$objectFieldName[0] = strtolower($objectFieldName[0]);
		}
		else
		{
			$objectFieldName = $databaseFieldName;
		}
		
		return $objectFieldName;		
	}
	
	/**
	 * Get the object field name for a particular database field.
	 * 
	 * @param string $databaseFieldName The name of the database field to get the object field name for.
	 * @return mixed The name of the object field, FALSE on failure.
	 */
	public function getObjectFieldName($databaseFieldName)
	{
		if(!empty($this->databaseTableInfo['fields'][$databaseFieldName]))
		{
			return $this->databaseTableInfo['fields'][$databaseFieldName]['objectFieldName'];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get the database field name for a particular object field.
	 * 
	 * @param string $objectFieldName The name of the object field to get the database field name for.
	 * @return mixed The name of the database field, FALSE on failure.
	 */
	public function getDatabaseFieldName($objectFieldName)
	{
		foreach($this->databaseTableInfo['fields'] as $field)
		{
			if($field['objectFieldName'] === $objectFieldName)
			{
				return $field['name'];
			}
		}
		return false;
	}
}
