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
	 * @var array $tables List of tables in this query.
	 */
	private $tables = [];
	
	/**
	 * Add a new table to the query.
	 * 
	 * @param string $name The name of the table to add.
	 * @param string $alias The alias to use for this table.
	 * @return self A reference to the current object.
	 */
	public function table($name, $alias)
	{
		$this->tables[] = [
			'name' => $name,
			'alias' => $alias,
			'conditions' => [
			],
		];
		return $this;
	}

	/**
	 * Add a join condition for the most recently added table.
	 * 
	 * If one parameter is specified, it will be treated as a field that must match between both tables. If more parameters are specified, they will identify the fields in each table, and the exact type of conditional (equal, lt, gt).
	 * 
	 * @param string|array $field1 The name or identity of the first field in the join condition.
	 * @param string|null $condType The type of join condition.
	 * @param array|null $field2 The identity of the second field in the join condition.
	 * @return self A reference to the current object.
	 * 
	 */
	public function cond($field1, $condType = null, $field2 = null)
	{
		
		$this->tables[key($this->tables)]['conditions'];
		return $this;
	}
}