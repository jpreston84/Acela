<?php
/**
 * Template for database schema tables.
 */

namespace Acela\Core\Database\Drivers;

use \Acela\Core;
use \Acela\Core\Database;

/**
 * Template for database schema tables.
 */
abstract class SchemaTable implements \Iterator
{
	/**
	 * Use the IterateItems trait.
	 */
	use Core\IterateItems;
	
	/**
	 * @var Schema $schema The schema object this table belongs to.
	 */
	public $schema;
	
	/**
	 * @var array $items An array of SchemaField objects.
	 */
	public $items = [];
	
	/**
	 *  @var array An array of SchemaConstraint objects. 
	 */
	public $constraints = [];
	
	/**
	 *  @var bool $new Is this schema table new?
	 */
	public $new = true;
	
	/**
	 *  @var bool $deleted Has this table been deleted?
	 */
	public $deleted = false;
	
	/**
	 *  @var string $name The name of this schema table.
	 */
	public $name;
	
	/**
	 * Load metadata about a table into this SchemaTable entry.
	 * 
	 * This method should only be called from Schema::get(), and should never
	 * be called directly.
	 * 
	 * @param string $tableName The name of the table to load.
	 */
	public function loadTable($tableName)
	{
		/**
		 * Get table data.
		 */
		$tableInfo = $this->schema->driver->getTableInfo($tableName);
		
		/**
		 * Generate fields.
		 */
		foreach($tableInfo['fields'] as $field)
		{
			/**
			 * Generate a new SchemaField.
			 */
			$schemaField = $this->getFieldInstance();
			
			/**
			 * Assign properties of the field.
			 */
			$schemaField->name = $field['name'];
			$schemaField->type = $field['type'];
			$schemaField->length = $field['length'];
			$schemaField->signed = $field['signed'];
			$schemaField->default = $field['default'];
			$schemaField->nullable = $field['nullable'];
			// $schemaField->primary = $field['primary'];
			$schemaField->autoIncrement = $field['autoIncrement'];
			$schemaField->positionFirst = $field['positionFirst'];
			$schemaField->positionAfter = $field['positionAfter'];
			$schemaField->setOriginalState(); // Set this as the original state of the field, so that any future changes will indicate that the field has been altered.
			$schemaField->new = false; // This is not a new field.
			
			/**
			 * Add the field to the stack of fields.
			 */
			$this->items[] = $schemaField;
		}	
		
		/**
		 * Generate constraints.
		 */
		foreach($tableInfo['constraints'] as $constraint)
		{
			/**
			 * Generate a new SchemaConstraint.
			 */
			$schemaConstraint = $this->getConstraintInstance();
			
			/**
			 * Assign properties of the field.
			 */
			$schemaConstraint->name = $constraint['name'];
			$schemaConstraint->type = $constraint['type'];
			$schemaConstraint->unique = $constraint['unique'];
			$schemaConstraint->fieldNames = $constraint['fieldNames'];
			$schemaConstraint->setOriginalState(); // Set this as the original state of the constraint, so that any future changes will indicate that the constraint has been altered.
			$schemaConstraint->new = false; // This is not a new constraint.
			
			/**
			 * Add the constraint to the stack of constraints.
			 */
			$this->constraints[] = $schemaConstraint;
		}
		
		/**
		 *  Set this table as not being new.
		 */
		$this->new = false;
		
		/**
		 *  Set the table name.
		 */
		$this->name = $tableName;
	}
	
	/**
	 *  Retrieve the SchemaField which has a particular name.
	 *  
	 *  @param string $name The name to match.
	 *  @return SchemaField A field that matched the name provided.
	 */
	public function get($name)
	{
		foreach($this->items as $field)
		{	
			if($field->name === $name)
			{
				return $field;
			}
		}
		
		return false;
	}
	
	/**
	 *  Delete the SchemaField specified.
	 *  
	 *  @param string $name The name of the field to delete.
	 */
	public function deleteField($name)
	{
		/**
		 *  Get the matching table field.
		 */
		$schemaField = $this->get($name);
		if(empty($schemaField))
		{
			Core\Error::critical('Unable to delete the field "'.$name.'" because such a field does not exist.');
		}
		
		/**
		 *  Delete the field.
		 */
		$schemaField->delete();
	}
	
	/**
	 *  Create a new field in the table.
	 *  
	 *  @param string $name The name of the field.
	 *  @param string $type The type of the field.
	 *  @param int $length The length, in bytes, of the field data.
	 *  @param bool $signed If an integer, is this a signed integer.
	 *  @param mixed $default The default value of this field.
	 *  @param bool $nullable Is the field nullable?
	 *  @param bool $primary Is this field the primary key?
	 *  @param bool $autoIncrement Is this field an auto-increment field?
	 *  @return The completed SchemaField.
	 */
	public function field($name, $type = null, $length = null, $signed = null, $default = null, $nullable = false, $autoIncrement = false, $position = false)
	{
		/**
		 *  Generate a new SchemaField.
		 */
		$schemaField = $this->getFieldInstance();
		
		/**
		 *  Assign properties of the field.
		 */
		$schemaField->name = $name;
		$schemaField->type = $type;
		$schemaField->length = $length;
		$schemaField->signed = $signed;
		$schemaField->default = $default;
		$schemaField->nullable = $nullable;
		$schemaField->autoIncrement = $autoIncrement;
		$schemaField->position = $position;

		/**
		 *  Add the field to the stack of fields.
		 */
		$this->items[] = $schemaField;

		/**
		 *  Return the created field.
		 */
		return $schemaField;
	}
	
	/**
	 *  Create a new boolean column.
	 *  
	 *  @param string $name The name of the column to be created.
	 *  @return The completed column.
	 */
	public function bool($name)
	{
		return $this->field($name, 'boolean');
	}	
	
	/**
	 *  Create a new integer column.
	 *  
	 *  @param string $name The name of the column to be created.
	 *  @return The completed column.
	 */
	public function int($name)
	{
		return $this->field($name, 'int', 4, true);
	}

	/**
	 *  Create a new big integer column.
	 *  
	 *  @param string $name The name of the column to be created.
	 *  @return The completed column.
	 */
	public function bigInt($name)
	{
		return $this->field($name, 'int', 8, true);
	}

	/**
	 *  Create a new date/time column.
	 *  
	 *  @param string $name The name of the column to be created.
	 *  @return The completed column.
	 */
	public function dateTime($name)
	{
		return $this->field($name, 'datetime');
	}
	
	/**
	 * Get a blank instance of the SchemaField for the current driver.
	 * 
	 * @return SchemaField A blank SchemaField.
	 */
	public function getFieldInstance()
	{
		$className = get_called_class();
		$className = substr($className, 0, -5).'Field';
		$schemaField = new $className;
		
		/**
		 * Attach the schema table to the schema field.
		 */
		$schemaField->schemaTable = $this;
		
		return $schemaField;
	}
	
	/**
	 *  Create a new constraint on this table.
	 *  
	 *  @param string $name The name of the constraint to create.
	 *  @param string $type The type of constraint.
	 *  @param bool $unique Is this constraint unique?
	 *  @param array $fieldNames The name of the fields to use in this constraint.
	 *  @return SchemaConstraint The completed constraint.
	 */
	public function constraint($name, $type = null, $unique = false, $fieldNames = [])
	{
		/**
		 *  Throw an error if a given constraint already exists.
		 */
		foreach($this->constraints as $schemaConstraint)
		{
			if($schemaConstraint->name === $name and !$schemaConstraint->deleted)
			{
				Core\Error::critical('Unable to create the database constraint "'.$name.'" on table "'.$this->name.'" because a constraint by that name already exists.');
			}
		}
		unset($schemaConstraint);
	
		/**
		 *  Generate a new SchemaConstraint.
		 */
		$schemaConstraint = $this->getConstraintInstance();
		
		/**
		 *  Assign properties to the constraint.
		 */
		$schemaConstraint->name = $name;
		$schemaConstraint->type = $type;
		$schemaConstraint->unique = $unique;
		$schemaConstraint->fieldNames = $fieldNames;
		
		/**
		 *  Add the constraint to the stack of constraints.
		 */
		$this->constraints[] = $schemaConstraint;
		
		/**
		 *  Return the created constraint.
		 */
		return $schemaConstraint;
	}
	
	/**
	 *  Get the named constraint or index from the table.
	 *  
	 *  @param string $name The name of the constraint or index to retrieve.
	 *  @return SchemaConstraint The desired SchemaConstraint.
	 */
	public function getConstraint($name)
	{
		foreach($this->constraints as $schemaConstraint) // For each constraint in the table...
		{
			if(
				!$schemaConstraint->deleted
				and $schemaConstraint->name === $name
			)
			{
				return $schemaConstraint;
			}
		}

		Core\Error::critical('Unable to get the constraint or index named "'.$name.'" from the table "'.$this->name.'", because that constraint does not exist.');
	}
	
	/**
	 *  Delete the named constraint or index from the table.
	 *  
	 *  @param string $name The name of the constraint or index to delete.
	 *  @return this A reference to the current SchemaTable.
	 */
	public function deleteConstraint($name)
	{
		$schemaConstraint = $this->getConstraint($name);
		$schemaConstraint->delete();

		return $this;
	}
	
	/**
	 * Get a blank instance of the SchemaConstraint for the current driver.
	 * 
	 * @return SchemaConstraint A blank SchemaConstraint.
	 */
	public function getConstraintInstance()
	{
		$className = get_called_class();
		$className = substr($className, 0, -5).'Constraint';
		$schemaConstraint = new $className;
		
		/**
		 * Attach the schema table to the schema constraint.
		 */
		$schemaConstraint->schemaTable = $this;
		
		return $schemaConstraint;
	}

	/**
	 *  Add a primary key constraint to the table.
	 *  
	 *  @param string $fieldNames The name(s) of the field(s) to use as the primary key.
	 *  @return A reference to the current table.
	 */
	public function primaryKey($fieldNames)
	{
		/**
		 *  Make sure $fieldNames is an array.
		 */
		if(!is_array($fieldNames))
		{
			$fieldNames = [ $fieldNames ];
		}
		
		/**
		 *  Add the constraint.
		 */
		$this->constraint('PRIMARY', 'primary', true, $fieldNames);
	
		return $this;
	}

	/**
	 *  Add an index constraint to the table.
	 *  
	 *  @param string $name The name of the index to create.
	 *  @param string $fieldNames The name(s) of the field(s) to use in the index.
	 *  @return A reference to the current table.
	 */
	public function index($name, $fieldNames)
	{
		/**
		 *  Make sure $fieldNames is an array.
		 */
		if(!is_array($fieldNames))
		{
			$fieldNames = [ $fieldNames ];
		}

		/**
		 *  Add the constraint.
		 */
		$this->constraint($name, 'index', false, $fieldNames);
	
		return $this;
	}
	
	/**
	 *  Delete this table from the schema.
	 *  
	 *  This method only flags the table for deletion. To actually delete the table,
	 *  you will also need to call ->save().
	 *  
	 *  @return this A reference to the curernt object.
	 */
	public function delete()
	{
		$this->deleted = true;
		
		return $this;
	}

	/**
	 *  Undo deletions.
	 *  
	 *  @return this A reference to the curernt object.
	 */
	public function undelete()
	{
		$this->deleted = false;
	}
	
	/**
	 *  Save the changes to this table.
	 *  
	 *  @return this A reference to the current object.
	 */
	public function save()
	{
		$this->saveSchemaChanges();
		$this->schema->clearTableCache($this->name);

		/**
		 *  If we didn't just delete the table, try to reload it to get all the changes.
		 */
		if(!$this->deleted)
		{
			$schemaTable = $this->schema->get($this->name, true, false);
			return $schemaTable;
		}
		else
		{
			return $this;
		}
	}
}
