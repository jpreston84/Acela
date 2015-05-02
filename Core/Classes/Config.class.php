<?php
/**
 * The global configuration object.
 */

namespace Acela\Core;

class Config
{
	/**
	 * Magic Method - Automatically load configuration data, etc.
	 * 
	 * @param string $name The name of the property to check.
	 * @return mixed The value of the requested property, after autoloading has happened, if needed.
	 */
	public function __get($name)
	{
		if(empty($this->$name))
		{
			$filename = __DIR__.'/../../Config/'.$name.'.config.php';
			if(file_exists($filename))
			{
				require_once $filename; 
			}
		}
		
		return $this->$name;
	}
}
