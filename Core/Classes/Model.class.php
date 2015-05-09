<?php
/**
 * The main model manager.
 */

namespace Acela\Core;

/**
 * The main model manager.
 */
class Model
{
	/**
	 * Return the singleton manager for the specified model type.
	 * 
	 * @param string $name The model type to get the manager for.
	 * @return Models\Manager A manager for the selected model type.
	 */
	public static function getInstance($name)
	{
		$className = __NAMESPACE__.'\Models\\'.$name.'\Manager';
		if(class_exists($className)) // If files exist for this class...
		{
			$instance = $className::getInstance(); // Get the global instance of the class.
			return $instance;
		}
		else // This class does not exist -- attempt to create a Generic object.
		{
			$instance = new Models\Generic\Manager::getInstance($name);
			return $instance;
		}
	}
}
