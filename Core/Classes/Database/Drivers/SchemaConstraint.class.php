<?php
/**
 * Template for database schema table constraints.
 */

namespace Acela\Core\Database\Drivers;

use \Acela\Core;
use \Acela\Core\Database;

/**
 * Template for database schema table constraints.
 */
abstract class SchemaConstraint
{
	/**
	 * @var SchemaTable $schemaTable The SchemaTable this constraint belongs to.
	 */
	public $schemaTable;
	
	/**
	 *  @var array $properties The properties of this constraint.
	 */
	protected $properties = [];
	
	/**
	 *  @var array $originalProperties The properties of this constraint in its original state.
	 */
	protected $originalProperties = [];
	
	/**
	 *  @var bool $new Is this a new constraint?
	 */
	public $new = true;

	/**
	 *  @var bool $deleted Has this constraint been deleted from the schema?
	 */
	public $deleted = false;

	/**
	 *  @var bool $altered Have any of the properties of this constraint been altered?
	 */
	public $altered = false;

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
			Core\Error::warning('The value of property "'.$name.'" is undefined.');
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
		$this->altered = true; // The object has been altered.
	}
	
	/**
	 * Make the "original state" of the object match the current state.
	 * 
	 * This will turn off the ->altered flag and copy all properties to
	 * ->originalProperties.
	 */
	public function setOriginalState()
	{
		$this->altered = false;
		$this->originalProperties = $this->properties;
	}
	
	/**
	 *  Remove this constraint from the schema.
	 *  
	 *  @return A reference to the current constraint.
	 */
	public function delete()
	{
		$this->deleted = true;
		$this->altered = true;

		return $this;
	}

	/**
	 *  Get or set the altered state of the object.
	 *  
	 *  @param bool $state The state, true or false, to set the altered parameter to (optional). If omitted, the method will simply return the current state.
	 *  @return bool|this If a $state parameter was provided, returns a reference to the current object. If not, returns the boolean state of the altered parameter.
	 */
	public function altered($state = null)
	{
		/**
		 *  If the optional argument was provided, set the altered state.
		 */
		if(
			$state === true
			or $state === false
		)
		{
			$this->altered = $state;
			return $this;
		}
		else
		{
			return $this->altered;
		}
	}
}
