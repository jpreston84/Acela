<?php
/**
 *  The data model manager template.
 */

namespace Acela\Core\Models;

use \Acela\Core as Core;

/**
 *  The data model manager template.
 */
abstract class Manager extends Core\GlobalInstance
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
	 * Constructor -- Creates an instance of this manager.
	 */
	public function __construct()
	{
		/**
		 * Initialize this manager.
		 */
		$this->initialize();
	}
	
	/**
	 * Initialize the current manager, assigning various properties.
	 * 
	 * This is called from ->__construct(), and also from the Generic model. It
	 * may not be called from anywhere else.
	 * 
	 * @return [type] Return_Description
	 */
	public function initialize()
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
		 * If the model name has already been set, don't continue;
		 */
		if(!empty($this->modelName))
		{
			return;
		}
		
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
		/**
		 * If the database name has already been set, don't continue;
		 */
		if(!empty($this->databaseTableName))
		{
			return;
		}
		
		$this->databaseTableName = $this->modelName;
		$this->databaseTableName[0] = strtolower($this->databaseTableName[0]);
		$this->databaseTableName = Core\wordPluralize($this->databaseTableName);
	}

	/**
	 * Set the database field prefix.
	 */
	protected function setDatabaseFieldPrefix()
	{
		/**
		 * If the database field prefix has already been set, don't continue;
		 */
		if(!empty($this->databaseFieldPrefix))
		{
			return;
		}
		
		$this->databaseFieldPrefix = $this->modelName;
		$this->databaseFieldPrefix[0] = strtolower($this->databaseFieldPrefix[0]);
	}

	/**
	 * Create a new model of the type this Manager belongs to.
	 * 
	 * @return Model The created model.
	 */
	public function create()
	{
		/**
		 * Create the new Model.
		 */
		$className = __NAMESPACE__.'\\'.$this->modelName.'\Model';
		if(class_exists($className)) // If a model class exists, use it...
		{
			$model = new $className;
		}
		else // Otherwise, use a Generic model...
		{
			$model = new Generic\Model;
		}
		$model->_new = true;
		$model->_altered = true;
		$model->_manager = $this;
		
		/**
		 * Assign fields to the model based on default values.
		 */
		foreach($this->databaseTableInfo['fields'] as $field)
		{
			$model->{$field['objectFieldName']} = $field['default'];
		}
		
		/**
		 * Return the initialized model.
		 */
		return $model;
	}
	
	/**
	 * Get the first record matching the parameters specified.
	 * 
	 * @param array $params An array of parameters to use in selecting records.
	 * @return Model The first matching result.
	 */
	public function getFirst($params = [])
	{
		$resultSet = $this->get($params, 1);
		print_r($resultSet);
		foreach($resultSet as $result)
		{
			return $result;
		}
	}
	
	/**
	 * Get one or more records matching the provided parameters.
	 * 
	 * @param array $params An array of parameters to use in selecting records.
	 * @param int $qty The number of records to retrieve.
	 * @return ResultSet A set of results.
	 */
	public function get($params = [], $qty = 0)
	{
		$query = $GLOBALS['core']->db->query();
		
		/**
		 * Add params to query.
		 */
		$this->addParamsToQuery($params, $query);
		
		/**
		 * Add table name to query.
		 */
		$query->table($this->databaseTableName, 't1');
		
		/**
		 * Add quantity of records to query.
		 */
		if($qty)
		{
			$query->quantity($qty);
		}

		/**
		 * Run query and get results.
		 */
		$results = $query->run();

		/**
		 * Create a ResultSet.
		 */
		$resultSet = $this->createResultSet($results);
		
		/**
		 * Return the completed ResultSet.
		 */
		return $resultSet;
	}
	
	/**
	 * Create a ResultSet object and load the database results into it.
	 * 
	 * @param \Acela\Core\Database\Drivers\ResultSet $results A database result set.
	 * @return ResultSet A model result set.
	 */
	protected function createResultSet($results)
	{
		/**
		 * Get the name of the ResultSet class to be used, either named, or Generic.
		 */
		$className = get_called_class(); // Get the name of the called class, something like \Acela\Core\Models\User\Manager.
		$className = explode('\\', $className); // Explode into an array.
		$className = $className[count($className) - 2]; // Retrieve the penultimate entry from the class path (something like User or Generic), which should be the actual name of the model in use.
		$className = __NAMESPACE__.'\\'.$className.'\ResultSet'; // Build the complete class name of the ResultSet we want to load.
		
		/**
		 * Create an instance of the corresponding ResultSet.
		 */
		$resultSet = new $className;
		$resultSet->manager = $this; // Set a pointer in the ResultSet to the appropriate manager instance.

		/**
		 * Load the results.
		 */
		$resultSet->databaseResultSet = $results; // Add the database ResultSet object into the new model ResultSet.
		$resultSet->loadResultSet(); // Load entries from the database results.
		
		return $resultSet;
	}
	
	/**
	 * Add the parameters to the database query.
	 * 
	 * @param array $params A list of parameters to add.
	 * @param \Acela\Core\Database\Drivers\Query $query A database query to add the parameters to.
	 */
	protected function addParamsToQuery($params, $query)
	{
		foreach($params as $paramName => $param) // For each parameter passed.
		{
			/**
			 * If only one value in the param, use the key as the field name, and assume we
			 * want to use = as the operator.
			 */
			if(count($param) == 1)
			{
				$fieldName = $paramName;
				$operator = '=';
				$value = $param;
			}
			/**
			 * If two values are in the param, the first is the field name, the second is
			 * the value. We assume = is the operator.
			 */
			elseif(count($param) == 2)
			{
				$fieldName = $param[0];
				$operator = '=';
				$value = $param[1];
			}
			/**
			 * Otherwise, assume there are 3 values in the param, with the middle one being
			 * the operator.
			 */
			else
			{
				$fieldName = $param[0];
				$operator = $param[1];
				$value = $param[2];
			}
			
			/**
			 * Convert field name to database name.
			 */
			$fieldName = $this->getDatabaseFieldName($fieldName);
			
			/**
			 * Add the appropriate WHERE clause.
			 */
			$query->where('t1', $fieldName, $operator, $value);
		}
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
	
	/**
	 * Get the object field name of the primary key of this object type.
	 * 
	 * @return string The name of the object field for the primary key.
	 */
	public function getPrimaryKey()
	{
		foreach($this->databaseTableInfo['fields'] as $field)
		{
			if($field['primary'])
			{
				return $field['objectFieldName'];
			}
		}
		return false;
	}
}
