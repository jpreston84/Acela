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
	 * @var bool $_altered Has this object been altered or not since it was loaded?
	 */
	protected $_altered = false;
	
	/**
	 * @var bool $_new Is this a new record or an existing one?
	 */
	public $_new = false;

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
		$this->properties[$name] = $value; // Set the property.
		$this->_altered = true; // The object has been altered.
	}
	
	/**
	 * Save the current model.
	 */
	public function save()
	{
		/**
		 * If the object hasn't been altered, don't save.
		 */
		if(!$this->_altered)
		{
			return;
		}
		
		/**
		 * Update the createdOn/modifiedOn times and users.
		 */
		$this->setTimestamps();
		
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
			$query->set($databaseFieldName, $value);
		}
		
		/**
		 * Add WHERE clause based on primary key.
		 */
		foreach($this->properties as $property => $value) // For every property in this instance...
		{
			$databaseFieldName = $this->_manager->getDatabaseFieldName($property);
			if($this->_manager->databaseTableInfo['fields'][$databaseFieldName]['primary']) // If this is the primary key for the table...
			{
				$query->where(null, $databaseFieldName, '=', $value);
			}
		}
		
		/**
		 * Run the UPDATE query.
		 */
		$query->run();
		
		return;
	}
	
	/**
	 * Update the createdOn/modifiedOn datetime fields and users.
	 * 
	 * This function should only be called by ->save().
	 */
	protected function setTimestamps()
	{
		/**
		 * Update createdOn time.
		 */
		if(
			property_exists($this, 'createdOn') // If a createdOn field exists...
			and $this->_new // And this is a new record that hasn't been saved yet...
		)
		{
			$this->createdOn = date('Y-m-d H:i:s'); // Set the creation datetime to the current datetime.
		}
		
		/**
		 * Update modifiedOn time.
		 */
		if(
			property_exists($this, 'modifiedOn') // If a modifiedOn field exists...
		)
		{
			$this->modifiedOn = date('Y-m-d H:i:s'); // Set the modification datetime to the current datetime.
		}
		
		/**
		 * Update createdBy user.
		 */
		if(
			property_exists($this, 'createdBy') // If a createdBy field exists...
			and $this->_new // And this is a new record that hasn't been saved yet...
		)
		{
			$this->createdBy = Core\User::getInstance()->id;
		}

		/**
		 * Update modifiedBy user.
		 */
		if(
			property_exists($this, 'modifiedBy') // If a modifiedBy field exists...
		)
		{
			$this->modifiedBy = Core\User::getInstance()->id;
		}
	}	
}
