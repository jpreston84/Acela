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
	 *  Get a complete MySQL statement to change or generate this table.
	 *  
	 *  @return string The completed SQL statement which will make the necessary changes to this table.
	 */
	public function getSchemaChanges()
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
		
		$query = implode(', ', $query);
		
		return $query;
	}
	
	/**
	 *  Save the schema changes.
	 */
	public function saveSchemaChanges()
	{
		$changes = $this->getSchemaChanges();
		
		/**
		 *  Handle DROP TABLE statements.
		 */
		if($this->deleted)
		{
			$query = 'DROP TABLE `'.$this->name.'`';
		}
		/**
		 *  Set up CREATE TABLE or ALTER TABLE statement.
		 */
		elseif($this->new)
		{
			$query = 'CREATE TABLE `'.$this->name.'` ';
		}
		else
		{
			$query = 'ALTER TABLE `'.$this->name.'` ';
		}
		
		/**
		 *  Add field changes.
		 */
		$query .= '('.$changes.')';
		
		/**
		 *  End query.
		 */
		$query .= ';';
		
		echo $query;
	}
}
