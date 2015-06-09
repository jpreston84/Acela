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
			$schemaField->primary = $field['primary'];
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
	public function field($name, $type = null, $length = null, $signed = null, $default = null, $nullable = false, $primary = false, $autoIncrement = false, $position = false)
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
		$schemaField->primary = $primary;
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
	 *  Create a new integer column.
	 *  
	 *  @param string $name The name of the column to be created.
	 *  @return The completed column.
	 */
	public function int($name)
	{
		return $this->field($name, 'int', 4, true, null, false, false, false);
	}

	/**
	 *  Create a new big integer column.
	 *  
	 *  @param string $name The name of the column to be created.
	 *  @return The completed column.
	 */
	public function bigint($name)
	{
		return $this->field($name, 'int', 8, true, null, false, false, false);
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
	 *  Delete this table from the schema.
	 *  
	 *  This method only flags the table for deletion. To actually delete the table,
	 *  you will also need to call ->save().
	 */
	public function delete()
	{
		$this->deleted = true;
	}

	/**
	 *  Undo deletions.
	 */
	public function undelete()
	{
		$this->deleted = false;
	}
	
	/**
	 *  Save the changes to this table.
	 */
	public function save()
	{
		$this->saveSchemaChanges();
	}
}
