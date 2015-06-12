<?php
/**
 * Database schema table constraint handler for MySQL.
 */

namespace Acela\Core\Database\Drivers\MySQL;

use \Acela\Core;
use \Acela\Core\Database;
use \Acela\Core\Database\Drivers;

/**
 * Database schema table constraint handler for MySQL.
 */
class SchemaConstraint extends Drivers\SchemaConstraint
{
	/**
	 *  Get a MySQL statement, or segment of a statement which defines this constraint.
	 *  
	 *  @return string The completed SQL statement which will create or modify this constraint.
	 */
	public function getSchemaChanges()
	{
		/**
		 *  Determine original constraint name.
		 */
		if($this->new)
		{
			$originalName = $this->properties['name'];
		}
		else // If this is not a new constraint, the original constraint must have been loaded from the database...
		{
			$originalName = $this->originalProperties['name'];
		}
	
		/**
		 *  Handle deleted constraints.
		 */
		if($this->deleted)
		{
			if($this->properties['type'] === 'primary')
			{
				return 'DROP PRIMARY KEY';
			}
			else
			{
				return 'DROP INDEX `'.$this->originalName.'`';
			}
		}
		
		/**
		 *  Handle new constraints.
		 */
		if($this->new)
		{
			return 'ALTER TABLE `'.$this->schemaTable->name.'` ADD '.$this->getMySQLDefinitionIndexNameAndType($originalName).' (`'.implode('`, `', $this->properties['fieldNames']).'`);';
		}
		
		/**
		 *  Handle altered constraints.
		 */
		if($this->altered)
		{
			Core\Error::critical('Reached altered state on SchemaConstraint object. If you want to alter a constraint, call the SchemaConstraint::delete() method on the old constraint, and then create a new constraint.');
		}
		
		/**
		 *  No changes, return nothing.
		 */
		return;
	}
	
	/**
	 *  Get the MySQL definition string for index names and types.
	 *  
	 *  @return The MySQL definition string.
	 */
	private function getMySQLDefinitionIndexNameAndType($name)
	{
		if($this->properties['type'] === 'primary')
		{
			return 'PRIMARY KEY';
		}
		elseif($this->properties['unique'])
		{
			return 'UNIQUE INDEX `'.$name.'` USING BTREE';
		}
		else
		{
			return 'INDEX `'.$name.'` USING BTREE';
		}
	}
}
