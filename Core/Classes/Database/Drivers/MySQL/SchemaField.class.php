<?php
/**
 * Database schema table field handler for MySQL.
 */

namespace Acela\Core\Database\Drivers\MySQL;

use \Acela\Core;
use \Acela\Core\Database;
use \Acela\Core\Database\Drivers;

/**
 * Database schema table field handler for MySQL.
 */
class SchemaField extends Drivers\SchemaField
{
	/**
	 *  Get a segment of a MySQL statement which defines this field.
	 *  
	 *  @return string The completed SQL segment which will create or modify this field.
	 */
	public function getSchemaChanges()
	{
		/**
		 *  Determine original field name.
		 */
		if($this->new)
		{
			$originalName = $this->properties['name'];
		}
		else // If this is not a new field, the original name must have been loaded from the database...
		{
			$originalName = $this->originalProperties['name'];
		}
	
		/**
		 *  Handle deleted fields.
		 */
		if($this->deleted) // If this field has been deleted...
		{
			if($this->schemaTable->new) // If this is a new schema table...
			{
				return; // Do nothing, just don't add this field to the table query.
			}
			else // This is an existing table...
			{
				return 'DROP COLUMN `'.$originalName.'`';
			}
		}
	
		/**
		 *  Set up new table column, ADD COLUMN, or CHANGE COLUMN syntax.
		 */
		if($this->schemaTable->new)
		{
			$query = '';
		}
		elseif($this->new)
		{
			$query = 'ADD COLUMN';
		}
		elseif($this->altered)
		{
			$query = 'CHANGE COLUMN `'.$originalName.'`';
		}
		else
		{
			return;
		}
		
		$query .= ' `'.$this->properties['name'].'` '.$this->getMySQLDefinitionDataType().' '.$this->getMySQLDefinitionNullable().' '.$this->getMySQLDefinitionDefaultValue().' '.$this->getMySQLDefinitionAutoIncrement().' '.$this->getMySQLDefinitionPrimaryKey().' '.$this->getMySQLDefinitionPosition();
		
		return $query;
	}
	
	/**
	 *  Get the MySQL definition string for the data type for this field.
	 *  
	 *  @return The MySQL definition string.
	 */
	private function getMySQLDefinitionDataType()
	{
		/**
		 *  Integer types.
		 */
		if($this->properties['type'] === 'int')
		{
			if($this->properties['length'] === 1)
			{
				$type = 'TINYINT';
			}
			elseif($this->properties['length'] === 2)
			{
				$type = 'SMALLINT';
			}
			elseif($this->properties['length'] === 3)
			{
				$type = 'MEDIUMINT';
			}
			elseif($this->properties['length'] === 4)
			{
				$type = 'INT';
			}
			elseif($this->properties['length'] === 8)
			{
				$type = 'BIGINT';
			}
			else
			{
				Core\Error::critical('Invalid data length specified for MySQL integer field. Valid lengths are only 1, 2, 3, 4 or 8 bytes.', null, ['field' => $this]);
			}
			
			/**
			 *  Include the UNSIGNED keyword if appropriate.
			 */
			if(!$this->properties['signed'])
			{
				$type .= ' UNSIGNED';
			}
			
			return $type;
		}
		
		/**
		 *  Date/Time types.
		 */
		if($this->properties['type'] === 'datetime')
		{
			return 'DATETIME';
		}
		
		/**
		 *  Varchar type.
		 */
		if($this->properties['type'] === 'text' and $this->properties['length'] < 2^8 - 1)
		{
			return 'VARCHAR('.$this->properties['length'].')';
		}
		
		/**
		 *  Text types.
		 */
		if($this->properties['type'] === 'text')
		{
			if($this->properties['length'] == 2^8 - 1)
			{
				$type = 'TINYTEXT';
			}
			elseif($this->properties['length'] == 2^16 - 1)
			{
				$type = 'TEXT';
			}
			elseif($this->properties['length'] == 2^24 - 1)
			{
				$type = 'MEDIUMTEXT';
			}
			elseif($this->properties['length'] == 2^32 - 1)
			{
				$type = 'LONGTEXT';
			}
			else
			{
				Core\Error::critical('Invalid data length specified for MySQL text field. Valid lengths are only 2^8-1, 2^16-1, 2^24-1, or 2^32-1.', null, ['field' => $this]);
			}
			
			return $type;
		}
		
		Core\Error::critical('Unable to determine MySQL data type for field type "'.$this->properties['type'].'".', null, ['field' => $this]);
	}
	
	/**
	 *  Get the MySQL definition string for whether this field is nullable or not.
	 *  
	 *  @return The MySQL definition string.
	 */
	private function getMySQLDefinitionNullable()
	{
		if($this->properties['nullable'])
		{
			return 'NULL';
		}
		else
		{
			return 'NOT NULL';
		}
	}
	
	/**
	 *  Get the MySQL definition string for the default value of this field.
	 *  
	 *  @return The MySQL definition string.
	 */
	private function getMySQLDefinitionDefaultValue()
	{
		if($this->properties['autoIncrement'] === true) // If this is an auto-increment column, no default value is allowed...
		{
			return; // Return an empty string, indicating no default value.
		}
		elseif($this->properties['default'] === true)
		{
			return 'DEFAULT TRUE';
		}
		elseif($this->properties['default'] === false)
		{
			return 'DEFAULT FALSE';
		}
		elseif(empty($this->properties['default']) and $this->properties['nullable'])
		{
			return 'DEFAULT NULL';
		}
		elseif($this->properties['type'] === 'int')
		{
			return 'DEFAULT '.(int) $this->properties['default'];
		}
		else // Otherwise, make the string safe and return it...
		{
			return 'DEFAULT '.$this->schemaTable->schema->driver->safeString($this->properties['default']);
		}
	}

	/**
	 *  Get the MySQL definition string for the auto-increment status of this field.
	 *  
	 *  @return The MySQL definition string.
	 */
	private function getMySQLDefinitionAutoIncrement()
	{
		if($this->properties['autoIncrement'])
		{
			return 'AUTO_INCREMENT';
		}
	}

	/**
	 *  Get the MySQL definition string for the primary key status of this field.
	 *  
	 *  @return The MySQL definition string.
	 */
	private function getMySQLDefinitionPrimaryKey()
	{
		if($this->properties['primary'])
		{
			return 'PRIMARY KEY';
		}
	}
	
	/**
	 *  Get the MySQL definition string for the position of this field.
	 *  
	 *  @return The MySQL definition string.
	 */
	private function getMySQLDefinitionPosition()
	{
		if($this->properties['positionFirst'])
		{
			return 'FIRST';
		}
		elseif(!empty($this->properties['positionAfter']))
		{
			return 'AFTER `'.$this->properties['positionAfter'].'`';
		}
	}
}
