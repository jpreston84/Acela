<?php
/**
 * Database schema table for MySQL.
 */

namespace Acela\Core\Database\Drivers\MySQL;

use \Acela\Core;
use \Acela\Core\Database;
use \Acela\Core\Database\Drivers;

/**
 * Database schema tables for MySQL.
 */
class SchemaTable extends Drivers\SchemaTable
{
	/**
	 *  Copy a table.
	 *  
	 *  @param string $tableName The name of the new table to be created.
	 *  @return SchemaTable The new table.
	 */
	public function copy($tableName)
	{
		/**
		 *  Verify destination table does not exist.
		 */
		if($this->schema->driver->tableExists($tableName))
		{
			Core\Error::critical('Unable to copy table "'.$this->name.'" because destination table "'.$tableName.'" already exists.');
		}
	
		/**
		 *  Create a new schema table.
		 */
		$query = 'CREATE TABLE `'.$tableName.'` LIKE `'.$this->name.'`;';
		$this->schema->driver->engine->rawQuery($query); // Run the database query, creating the table.

		/**
		 *  Load the new schema table.
		 */
		$schemaTable = $this->schema->get($tableName);
		
		return $schemaTable;
	}

	/**
	 *  Get a MySQL statement segments to make all field changes necessary.
	 *  
	 *  @return string The completed SQL statement which will make the necessary changes to this table.
	 */
	public function getSchemaFieldChanges()
	{
		/**
		 *  Handle DROP TABLE.
		 */
		if($this->deleted)
		{
			return '';
		}
		
		$query = [];
		
		foreach($this->items as $field)
		{
			$query[] = $field->getSchemaChanges();
		}
		
		$query = array_filter($query);
		
		return $query;
	}
	
	/**
	 *  Get all the MySQL statements to add constraints.
	 *  
	 *  @return array An array of SQL statements.
	 */
	public function getSchemaNewConstraints()
	{
		/**
		 *  Collect all the new schema constraint changes.
		 */
		$queries = [];
		foreach($this->constraints as $schemaConstraint)
		{
			if(
				!$schemaConstraint->deleted
				and $schemaConstraint->altered()
				and $schemaConstraint->new
			)
			{
				$queries[] = $schemaConstraint->getSchemaChanges();
			}
		}
		unset($schemaConstraint);
		
		/**
		 *  Filter out empty changes.
		 */
		$queries = array_filter($queries);
		
		return $queries;
	}

	/**
	 *  Get all the MySQL statements to delete constraints.
	 *  
	 *  @return array An array of SQL statements.
	 */
	public function getSchemaDeletedConstraints()
	{
		/**
		 *  Collect all the deleted schema constraint changes.
		 */
		$queries = [];
		foreach($this->constraints as $schemaConstraint)
		{
			if($schemaConstraint->deleted)
			{
				$queries[] = $schemaConstraint->getSchemaChanges();
			}
		}
		unset($schemaConstraint);
		
		/**
		 *  Filter out empty changes.
		 */
		$queries = array_filter($queries);
		
		return $queries;
	}
	
	/**
	 *  Save the schema changes.
	 */
	public function saveSchemaChanges()
	{
		/**
		 *  Handle DROP TABLE statements.
		 */
		if($this->deleted)
		{
			$query = 'DROP TABLE `'.$this->name.'`;';
			$this->schema->driver->engine->rawQuery($query);
			return;
		}
		
		/**
		 *  Handle CREATE TABLE statements.
		 */
		elseif($this->new)
		{
			/**
			 *  Open query.
			 */
			$query = 'CREATE TABLE `'.$this->name.'` ';
			
			/**
			 *  Add field definitions.
			 */
			$fieldChanges = $this->getSchemaFieldChanges();
			if(!empty($fieldChanges))
			{
				$query .= '('.implode(', ', $fieldChanges).')';
			}
			
			/**
			 *  Close query.
			 */
			$query .= ';';
			
			/**
			 *  Run query.
			 */
			$this->schema->driver->engine->rawQuery($query);
		}

		/**
		 *  Handle ALTER TABLE statements.
		 */
		else
		{
			/**
			 *  Open query.
			 */
			$query = 'ALTER TABLE `'.$this->name.'` ';
			
			/**
			 *  Add field definitions.
			 */
			$fieldChanges = $this->getSchemaFieldChanges();

			/**
			 *  Get query segments to remove constraints.
			 */
			$constraintChanges = $this->getSchemaDeletedConstraints();
			
			/**
			 *  Merge all changes into one array.
			 */
			$changes = array_merge($fieldChanges, $constraintChanges);
			
			if(!empty($changes))
			{
				$query .= implode(', ', $changes);
			}

			/**
			 *  Close query.
			 */
			$query .= ';';
			
			/**
			 *  Run query.
			 */
			$this->schema->driver->engine->rawQuery($query);
		}
		
		/**
		 *  Add constraints.
		 */
		$constraints = $this->getSchemaNewConstraints();
		foreach($constraints as $constraint)
		{
			$this->schema->driver->engine->rawQuery($constraint);
		}
	}
}
