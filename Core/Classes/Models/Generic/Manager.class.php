<?php
/**
 *  The data model manager for generic objects that have not been otherwise defined.
 */

namespace Acela\Core\Models\Generic;

use \Acela\Core\Models as Models;

/**
 *  The data model manager for generic objects that have not been otherwise defined.
 */
class Manager extends Models\Manager
{
	/**
	 * Return the global instance of this class.
	 * 
	 * It is important to note that this is not the only means of creating an
	 * instance of the manager. However, it is recommended that the global
	 * instance be used whenever possible.
	 * 
	 * Additionally, this particular ->getInstance() method takes an optional
	 * parameter specifying the name of the model, which is not found in the parent
	 * class version of this method.
	 * 
	 * @param string $name The name of the model for which to get a global instance of a manager.
	 * @return Manager A global instance of this class.
	 */
	public static function getInstance($name)
	{
		static $instances = array;
		
		/**
		 * Create the global instance if it hasn't been created yet.
		 */
		if(is_null($instances[$name])) // If the global instance hasn't been created yet...
		{
			$instances[$name] = new static(); // Create a new global instance of the class that was called (uses late static bindings).
			$instances[$name]->modelName = $name; // Change the model name from "Generic" to whatever we wanted an instance of.
			$instances[$name]->databaseTableName = null; // Unset the database table name, so it can be re-initialized below.
			$instances[$name]->databaseFieldPrefix = null; // Unset the database field prefix, so it can be re-initialized below.
			$instances[$name]->initialize(); // Re-initialize table values, etc, based on new model name.
		}
		
		return $instance;
	}
}
