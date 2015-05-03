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
	}
}
