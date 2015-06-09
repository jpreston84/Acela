<?php
/**
 *  The data model manager for generic objects that have not been otherwise defined.
 */

namespace Acela\Core\Models\Generic;

use \Acela\Core as Core;
use \Acela\Core\Models as Models;

/**
 *  The data model manager for generic objects that have not been otherwise defined.
 */
class Manager extends Models\Manager
{
	/**
	 *  Override the Manager template's ->getInstance() method.
	 *  
	 *  Unlike all other managers, the generic manager will only ever be
	 *  instantiated from \Acela\Core\Model->getInstance(), and will always need an
	 *  additional parameter. Therefore, the normal inherited ->getInstance() method
	 *  should never be used, and will return a critical error. Instead, the method
	 *  ->getInstanceOf($name) will be used.
	 */
	public static function getInstance()
	{
		Core\Error::critical('You cannot directly instantiate the Generic model manager. Please use Core\Model::getInstance($name) instead.');
	}

	/**
	 * Return the global instance of this class.
	 * 
	 * Please see the documentation for \Acela\Core\Models\Manager::getInstance().
	 * 
	 * Additionally, this particular method takes a required parameter
	 * specifying the name of the model, which is not found in the parent class
	 * version of this method.
	 * 
	 * @param string $name The name of the model for which to get a global instance of a manager.
	 * @return Manager A global instance of this class.
	 */
	public static function getInstanceOf($name)
	{
		static $instances = [];
		
		/**
		 * Create the global instance if it hasn't been created yet.
		 */
		if(!array_key_exists($name, $instances)) // If the global instance hasn't been created yet...
		{
			$instances[$name] = new static(); // Create a new global instance of the class that was called (uses late static bindings).
			$instances[$name]->modelName = $name; // Change the model name from "Generic" to whatever we wanted an instance of.
			$instances[$name]->databaseTableName = null; // Unset the database table name, so it can be re-initialized below.
			$instances[$name]->databaseFieldPrefix = null; // Unset the database field prefix, so it can be re-initialized below.
			$instances[$name]->initialize(); // Re-initialize table values, etc, based on new model name.
		}
		
		return $instances[$name];
	}
}
