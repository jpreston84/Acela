<?php
/**
 * A base class for Global Instance classes.
 */

namespace Acela\Core;

/**
 * A base class for Global Instance classes.
 * 
 * This is not technically a singleton base class because singletons can only
 * have one global instance. This class merely provides for the static
 * className::getInstance() method, which will return a global instance.
 */
abstract class GlobalInstance
{
	/**
	 * Return the global instance of this class.
	 * 
	 * It is important to note that this is not the only means of creating an
	 * instance of the manager. However, it is recommended that the global
	 * instance be used whenever possible.
	 * 
	 * @return Manager A global instance of this class.
	 */
	public static function getInstance()
	{
		static $instance;
		
		/**
		 * Create the global instance if it hasn't been created yet.
		 */
		if(is_null($instance)) // If the global instance hasn't been created yet...
		{
			$instance = new static(); // Create a new global instance of the class that was called (uses late static bindings).
		}
		
		return $instance;
	}
}