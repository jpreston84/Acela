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
	 * @var Driver $driver A reference to the instantiated database driver.
	 */
	public $driver;
	
	/**
	 * @var array $tables List of tables in this query.
	 */
	protected $tables = [];

	/**
	 * @var array $selects List of columns to be selected by this query.
	 */
	protected $selects = [];
	
	/**
	 * @var array $wheres List of where conditions in this query.
	 */
	protected $wheres = [];

	/**
	 * @var string|null $nextOperator The operator to use for the next ->cond() or ->where().
	 */
	protected $nextOperator = null;
	
	/**
	 * @var int $groupDepth What depth are we in in the current group structure? 0 for no grouping.
	 */
	protected $groupDepth = 0;
	
	/**
	 * @var array $groupContents Contents of the current group of clauses.
	 */
	protected $groupContents = [];
	
	/**
	 * @var array $queryData Completed data for running a query.
	 */
	protected $queryData = null;
	
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
			'conditions' => [],
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
				$this->tables[count($this->tables) - 2]['alias'], // Get second-to-last added table and find its alias.
				$tmpFieldName,
			];
			$condType = '=';
			$field2 = [
				$this->tables[count($this->tables) - 1]['alias'], // Get last added table and find its alias.
				$tmpFieldName,
			];
		}
		
		$this->tables[key($this->tables)]['conditions'] = [
			'field1' => $field1,
			'matchType' => $condType,
			'field2' => $field2
		]; // Add the condition to the table it applies to.
		return $this;
	}
	
	/**
	 * Specify a field or set of fields to select from the tables.
	 * 
	 * @param string $tableAlias The alias of the table the fields should be retrieved from.
	 * @param string $name The name of the field to be retrieved from.
	 * @param string $alias An alias for the retrieved data.
	 * @return self A reference to the current object.
	 */
	public function select($tableAlias, $name, $alias = null)
	{
		/**
		 * If no alias provided, use field name.
		 */
		if(empty($alias))
		{
			$alias = $name;
		}
		
		$this->selects[] = [
			'table' => $tableAlias,
			'field' => $name,
			'alias' => $alias,
		];
		
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
		$tmpWhere = [
			'alias' => $alias,
			'name' => $name,
			'matchType' => $matchType,
			'value' => $value,
			'type' => 'AND',
		];
		
		if($this->nextOperator != null)
		{
			$tmpWhere['type'] = $this->nextOperator;
			$this->nextOperator = null;
		}
		
		/**
		 * Place the conditional into the appropriate group structure, if applicable.
		 */
		if($this->groupDepth > 0) // If we're in a group...
		{
			$tmpGroupDepth = $this->groupDepth; // Get the depth we need to access to in the ->groupContents array.
			$tmpGroupData = &$this->groupContents;
			while($tmpGroupDepth > 0) // For each level of group depth we need to get to...
			{
				$tmpKeys = array_keys($tmpGroupData);
				$tmpGroupData = &$tmpGroupData[end($tmpKeys)];
				$tmpGroupDepth--;
			}
			$tmpGroupData[] = $tmpWhere; // Add the where clause to the end of the current group.
		}
		else
		{
			$this->wheres[] = $tmpWhere; // Add the where clause to the list of where clauses.
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
	
	/**
	 * Start a group of conditions.
	 * 
	 * If you need to subgroup your conditions, either for join clauses or for where
	 * clauses, simply execute ->group() multiple times. For instance, you may do...
	 * 
	 * $db
	 *      ->query()
	 *      ->table('table1', 't1')
	 *      ->group()
	 *           ->where('t1', 'foo', '=', 'bar')
	 *           ->o()
	 *           ->group()
	 *                ->where('t1', 'foo', '=', 'aha')
	 *                ->a()->where('t1', 'bar', '=', true)
	 *           ->groupEnd()
	 * 	    ->groupEnd()
	 *      ->o()->where('t1', 'baz', '=', false)
	 * ;
	 * 
	 * @return self A reference to the current object.
	 */
	public function group()
	{
		$this->groupDepth++; // Go one level deeper in the group structure.
		$this->addArrayAtDepth($this->groupDepth);
		return $this;
	}
	
	/**
	 * Append a new array to the end of a multi-dimensional array, at the specified
	 * depth.
	 * 
	 * @param int $depth The depth at which to add the new array, 1-indexed.
	 */
	protected function addArrayAtDepth($depth)
	{
		echo 'Before add array <br />';
		print_r($this->groupContents);
		echo '<br />';
		
		$depth--; // We start at depth 1 in the array, so we don't need to process that.
		
		$currentRef = &$this->groupContents;

		echo 'Selected...<br />';
		print_r($currentRef);
		echo '<br />';

		while($depth > 0) // For each level of depth...
		{
			/**
			 * Get last member at the current level.
			 */
			$keys = array_keys($currentRef);
			$newRef = &$currentRef[end($keys)];
			unset($currentRef);
			$currentRef = &$newRef;
			unset($newRef);
			$depth--;

			echo 'Selected...<br />';
			print_r($currentRef);
			echo '<br />';
		}
		
		/**
		 * Add a new array to the end of the selected array element.
		 */
		$currentRef[] = [];
		
		echo 'After add array <br />';
		print_r($this->groupContents);
		echo '<br />';

	}
	
	/**
	 * End a group of conditions.
	 * 
	 * @return self A reference to the current object.
	 */
	public function groupEnd()
	{
		$this->groupDepth--; // Go one level up in the group structure.
		if(empty($this->groupDepth))
		{
			$this->wheres[] = $this->groupContents;
			$this->groupContents = [];
		}
		return $this;
	}
	
	/**
	 * Abstract function that builds the query for the selected database driver.
	 * 
	 * @return array A complete query, ready to be executed.
	 */
	abstract public function buildQuery();
	
	/**
	 * Abstract function that executes a query for the selected database driver.
	 * 
	 * @param array $queryData The data necessary to execute the query.
	 */
	abstract public function executeQuery(array $queryData);
	
	/**
	 * Run the query that's been constructed.
	 * 
	 * @return self A reference to the current object.
	 */
	public function run()
	{
		$this->queryData = $this->buildQuery();
		$this->executeQuery($this->queryData);
		
		return $this;
	}
}