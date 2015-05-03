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
	static function get($name)
	{
		$className = 'Models\\'.$name.'\Manager';
		$instance = $className::getInstance();
		return $instance;
	}
}
