<?php
/**
 * A class for creating database queries.
 */

namespace Acela\Core\Database\Drivers;

/**
 * A class for creating database queries.
 */
abstract class Query
{
	/**
	 * Add a new table to the query.
	 * 
	 * @param string $name The name of the table to add.
	 * @param string $alias The alias to use for this table.
	 * @return this A reference to the current object.
	 */
	public function table($name, $alias)
	{
		return $this;
	}
}