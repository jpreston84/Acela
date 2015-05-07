<?php
/**
 *  The data model template.
 */

namespace Acela\Core\Models;

use \Acela\Core as Core;

/**
 *  The data model template.
 */
abstract class Model
{
	/**
	 * @var Manager $_manager Manager for this model.
	 */
	public $_manager; // It is necessary to use $_manager rather than $manager to avoid having manager as a reserved property not usable in database tables.

	/**
	 * @var array $properties All the properties of this model.
	 */
	protected $properties = [];
	
	/**
	 * Magic Method - Get the value of a property of this object.
	 * 
	 * @param string $name The name of the property to get.
	 * @return mixed The value of the property.
	 */
	public function __get($name)
	{
		if(isset($this->properties[$name]))
		{
			return $this->properties[$name];
		}
		else
		{
	        $trace = debug_backtrace();
			trigger_error(
				'Undefined property via __get(): ' . $name .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_NOTICE
			);
			return null;
		}
	}

	/**
	 * Magic Method - Set a property of this object.
	 * 
	 * @param string $name The name of the property to set.
	 * @param mixed $value The value of the property.
	 */
	public function __set($name, $value)
	{
		$this->properties[$name] = $value;
	}
	
	/**
	 * Save the current model.
	 */
	public function save()
	{
		$query = $GLOBALS['core']->db->query()->update($this->_manager->databaseTableName);
		
		foreach($this->properties as $property => $value) // For every property in this instance...
		{
			$databaseFieldName = $this->_manager->getDatabaseFieldName($property);
			
			/**
			 * Exclude primary key property.
			 */
			if($this->_manager->databaseTableInfo['fields'][$databaseFieldName]['primary'])
			{
				continue; // Skip this property and move on to the next.
			}
			
			/**
			 * Add this field to the query.
			 */
			$query->set($property, $value);
		}
		
		/**
		 * Add WHERE clause based on primary key.
		 */
		foreach($this->properties as $property => $value) // For every property in this instance...
		{
			if($this->_manager->databaseTableInfo['fields'][$this->_manager->getDatabaseFieldName($property)]['primary'])
			{
				$query->where(null, $property, '=', $value);
			}
		}
		$query->build();
		print_r($query->queryData);
	}
}
