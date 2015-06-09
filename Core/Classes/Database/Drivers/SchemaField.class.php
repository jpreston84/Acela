<?php
/**
 * Template for database schema table fields.
 */

namespace Acela\Core\Database\Drivers;

use \Acela\Core;
use \Acela\Core\Database;

/**
 * Template for database schema table fields.
 */
abstract class SchemaField
{
	/**
	 * @var SchemaTable $schemaTable The SchemaTable this field belongs to.
	 */
	public $schemaTable;
	
	/**
	 *  @var array $properties The properties of this field.
	 */
	protected $properties = [];
	
	/**
	 *  @var array $originalProperties The properties of this field in its original state.
	 */
	protected $originalProperties = [];
	
	/**
	 *  @var bool $new Is this a new field?
	 */
	public $new = true;

	/**
	 *  @var bool $deleted Has this field been deleted from the schema?
	 */
	public $deleted = false;

	/**
	 *  @var bool $altered Have any of the properties of this object been altered?
	 */
	protected $altered = false;

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
	 *  Make the current field as the primary key.
	 *  
	 *  @return A reference to the current field.
	 */
	public function primary()
	{
		$this->properties['primary'] = true;
		$this->altered = true;
		
		/**
		 *  Unset primary status on all other fields.
		 */
		foreach($this->schemaTable as $schemaField)
		{
			if($schemaField->primary and $schemaField !== $this)
			{
				$schemaField->primary = false;
			}
		}
		
		return $this;
	}
	
	/**
	 *  Make the current field not the primary key.
	 *  
	 *  @return A reference to the current field.
	 */
	public function notPrimary()
	{
		$this->properties['primary'] = false;
		$this->altered = true;
		
		return $this;
	}

	/**
	 *  Make the current field an auto-increment field.
	 *  
	 *  @return A reference to the current field.
	 */
	public function autoIncrement()
	{
		$this->properties['autoIncrement'] = true;
		$this->altered = true;
		
		return $this;
	}
	/**
	 *  Make the current field not an auto-increment field.
	 *  
	 *  @return A reference to the current field.
	 */
	public function notAutoIncrement()
	{
		$this->properties['autoIncrement'] = false;
		$this->altered = true;
		
		return $this;
	}

	/**
	 *  Make the current field a signed field.
	 *  
	 *  @return A reference to the current field.
	 */
	public function signed()
	{
		$this->properties['signed'] = true;
		$this->altered = true;
		
		return $this;
	}

	/**
	 *  Make the current field an unsigned field.
	 *  
	 *  @return A reference to the current field.
	 */
	public function unsigned()
	{
		$this->properties['signed'] = false;
		$this->altered = true;
		
		return $this;
	}

	/**
	 *  Make the current field a nullable field.
	 *  
	 *  @return A reference to the current field.
	 */
	public function nullable()
	{
		$this->properties['nullable'] = true;
		$this->altered = true;
		
		return $this;
	}

	/**
	 *  Make the current field not a nullable field.
	 *  
	 *  @return A reference to the current field.
	 */
	public function notNullable()
	{
		$this->properties['nullable'] = false;
		$this->altered = true;
		
		return $this;
	}
	
	/**
	 *  Remove this field from the schema.
	 *  
	 *  @return A reference to the current field.
	 */
	public function delete()
	{
		/**
		 *  Check to see if this field is the only remaining non-deleted field.
		 */
		$remainingFields = 0;
		foreach($this->schemaTable as $schemaField) // For each field in the table...
		{
			if(
				$schemaField->name != $this->properties['name'] // If the name of the iterated field does not match the name of the current object...
				and !$schemaField->deleted // And the iterated field has not been deleted...
			)
			{
				$remainingFields++;
			}
		}
		if($remainingFields == 0) // If there are no other remaining fields, we can't delete this one...
		{
			Core\Error::critical('Unable to delete the field "'.$this->properties['name'].'" from table "'.$this->schemaTable->name.'", because it is the last field in that table. Use SchemaTable::delete() instead.');
		}
	
		$this->deleted = true;
		$this->altered = true;

		return $this;
	}

	/**
	 *  Undelete this field from the schema.
	 *  
	 *  @return A reference to the current field.
	 */
	public function undelete()
	{
		$this->deleted = false;
		$this->altered = true;

		return $this;
	}

	/**
	 *  Make the current field the first field in the table.
	 *  
	 *  @return A reference to the current object.
	 */
	public function first()
	{
		$this->properties['positionFirst'] = true;
		$this->properties['positionAfter'] = false;
		$this->altered = true;
		
		return $this;
	}

	/**
	 *  Position the current field after the specified field.
	 *  
	 *  @param string $fieldName The name of the field after which the current field should be positioned.
	 *  
	 *  @return A reference to the current object.
	 */
	public function after($fieldName)
	{
		$this->properties['positionFirst'] = false;
		$this->properties['positionAfter'] = $fieldName;
		$this->altered = true;
		
		return $this;
	}
}
