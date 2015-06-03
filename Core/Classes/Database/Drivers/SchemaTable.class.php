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
class SchemaTable implements \Iterator
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
		$tableInfo = $this->schema->driver->getTableInfo();
		
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
			$schemaField->name = $field->name;
			$schemaField->type = $field->type;
			$schemaField->length = $field->length;
			$schemaField->signed = $field->signed;
			$schemaField->default = $field->default;
			$schemaField->nullable = $field->nullable;
			$schemaField->primary = $field->primary;
			$schemaField->autoIncrement = $field->autoIncrement;
			
			/**
			 * Add the field to the stack of fields.
			 */
			$this->items[] = $schemaField;
		}
	}
	
	/**
	 * Get a blank instance of the SchemaField for the current driver.
	 * 
	 * @return SchemaField A blank SchemaField.
	 */
	public function getFieldInstance()
	{
		$className = get_called_class();
		$className = substr($className, -5).'Field';
		$schemaField = new $className;
		
		/**
		 * Attach the schema table to the schema field.
		 */
		$schemaField->schemaTable = $this;
		
		return $schemaField;
	}
}
