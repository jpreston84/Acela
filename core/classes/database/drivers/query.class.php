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
	 * @var array $wheres List of where conditions in this query.
	 */
	private $wheres = [];

	/**
	 * @var string|null $nextOperator The operator to use for the next ->cond() or ->where().
	 */
	private $nextOperator = null;
	
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
	public function cond($field1, $condType = null, array $field2 = null)
	{
		/**
		 * Determine if this block is a single field specifier, and if so,
		 * convert it to full form.
		 */
		if($condType == null and $field2 == null and is_string($field1))
		{
			$tmpFieldName = $field1; // Store a copy of the field name that must match.			
			$field1 = [
				$this->tables[key($this->tables) - 1]['alias'], // Get second-to-last added table and find its alias.
				$tmpFieldName,
			];
			$condType = '=';
			$field2 = [
				$this->tables[key($this->tables)]['alias'], // Get last added table and find its alias.
				$tmpFieldName,
			];
		}
		
		$this->tables[key($this->tables)]['conditions'] = [$field1, $condType, $field2]; // Add the condition to the table it applies to.
		return $this;
	}
	
	/**
	 * Add a where condition to the current query.
	 * 
	 * @param string $alias The alias of the table the target field is located in.
	 * @param string $name The name of the field we need to find a match in.
	 * @param string $matchType The type of match we're looking for. Can be =, <, >, <=, >=, !=, LIKE, IN.
	 * @param string|int|bool|array $value The value we are matching against.
	 * @return self A reference to the current object.
	 */
	public function where($alias, $name, $matchType, $value)
	{
		$this->wheres[] = [
			'alias' => $alias,
			'name' => $name,
			'matchType' => $matchType,
			'value' => $value,
			'type' => 'AND',
		];
		
		if($this->nextOperator != null)
		{
			$this->wheres[key($this->wheres)]['type'] = $this->nextOperator;
			$this->nextOperator = null;
		}
		
		return $this;
	}
	
	/**
	 * Set the next conditional statement to be added with an OR clause.
	 * 
	 * @return self A reference to the current object.
	 */
	public function o()
	{
		$this->nextOperator = 'OR';
		
		return $this;
	}
	
	/**
	 * Set the next conditional statement to be added with an AND clause.
	 * 
	 * @return self A reference to the current object.
	 */
	public function a()
	{
		$this->nextOperator = 'AND';
		
		return $this;
	}
	
	
}