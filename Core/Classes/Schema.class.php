<?php
/**
 * The main schema accessor.
 */

namespace Acela\Core;

use \Acela\Core;

/**
 * The main schema accessor.
 */
class Schema
{
	/**
	 * Return the singleton for the schema manager.
	 * 
	 * @return Database\Engine\Schema The schema manager for the default database connection.
	 */
	public static function getInstance()
	{
		$instance = Database\Engine::schema();

		return $instance;
	}
	
	/**
	 *  Magic Method - Runs calls on the default schema.
	 *  
	 *  This method allows you to run:
	 *  Core\Schema::get('tableName');
	 *  instead of:
	 *  Core\Database\Engine::schema()->get('tableName');
	 *  
	 *  @param string $name The name of the method to run on the schema.
	 *  @param array $arguments An array of arguments for the method.
	 *  @return Database\Schema A database schema object.
	 */
	public static function __callStatic($name, $arguments)
	{
		$instance = self::getInstance();
		return call_user_func_array([$instance, $name], $arguments);
	}
}
