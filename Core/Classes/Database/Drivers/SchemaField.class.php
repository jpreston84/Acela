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
class SchemaField
{
	/**
	 * @var SchemaTable $schemaTable The SchemaTable this field belongs to.
	 */
	public $schemaTable;
	
	/**
	 * @var string $name The name of the field.
	 */
	public $name;
	
	/**
	 * @var string $type The data type in the field.
	 */
	public $type;
	
	/**
	 * @var int $length For string types, the max length of the string. For int types, the number of bytes used.
	 */
	public $length;
	
	/**
	 * @var bool $signed Is this a signed field or not? Has no value for non-integer fields.
	 */
	public $signed = true;
	
	/**
	 * @var mixed $default The default value for this field in new records.
	 */
	public $default = null;
	
	/**
	 * @var bool $nullable Can this field store null values?
	 */
	public $nullable = true;
	
	/**
	 * @var bool $primary Is this field the primary key?
	 */
	public $primary = false;
	
	/**
	 * @var bool $autoIncrement Is this field an auto-increment field? Has no effect for non-integer fields.
	 */
	public $autoIncrement = false;
}
