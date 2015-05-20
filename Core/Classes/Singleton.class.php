<?php
/**
 * A base class for Singleton classes.
 */

namespace Acela\Core;

/**
 * A base class for Singleton classes.
 * 
 * This expands the GlobalInstance class, adding the ability to call methods
 * of a global instance via static operators. This is not a restricted
 * singleton, because you could technically create multiple instances of it.
 * When implementing this class, all methods which need to be accessible from
 * static context should be declared as private or protected.
 */
abstract class Singleton extends GlobalInstance
{
	/**
	 * Magic Method - Call static function. When a method is called statically, the
	 * global instance is located, and the function call is passed on.
	 * 
	 * @param string $name The name of the method to call.
	 * @param array $arguments Parameters for the call.
	 * @return mixed The results of the call.
	 */
	public static function __callStatic($name, $arguments)
	{
		$instance = static::getInstance();
		return call_user_func_array([$instance, $name], $arguments);
	}
	
	/**
	 * Magic method - Call functions. This is needed because of __callStatic.
	 * 
	 * @param string $name The name of the method to call.
	 * @param array $arguments Parameters for the call.
	 * @return mixed The results of the call.
	 */
	public function __call($name, $arguments)
	{
		return call_user_func_array([$this, $name], $arguments);
	}
}