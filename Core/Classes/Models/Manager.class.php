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
		$this->databaseTableName = strtolower($this->modelName);
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
		$className = __NAMESPACE__.'\\'.$this->modelName.'\ResultSet';
		$resultSet = new $className;
		
		return $resultSet;
	}
}
