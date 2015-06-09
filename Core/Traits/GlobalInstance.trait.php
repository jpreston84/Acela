<?php
/**
 * Trait for classes that need to reference one global instance of themselves.
 */

namespace Acela\Core;
 
/**
 * Trait for classes that need to reference one global instance of themselves.
 */
trait GlobalInstance
{
	/**
	 * Return the global instance of this class.
	 * 
	 * It is important to note that this is not the only means of creating an
	 * instance of the class. However, it is recommended that the global instance be
	 * used whenever possible.
	 * 
	 * @return self A global instance of this class.
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
