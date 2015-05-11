<?php
/**
 * A class for creating MySQL database queries.
 */

namespace Acela\Core\Database\Drivers\MySQL;

use \Acela\Core\Database as Database;

/**
 * A class for creating MySQL database queries.
 */
class Query extends Database\Drivers\Query
{
	/**
	 * Generate a MySQL query from the components that have been input into the Query class.
	 * 
	 * @return string A MySQL databse query, as a string.
	 */
	public function build()
	{
		/**
		 * Build SELECT, UPDATE, or INSERT segment.
		 */
		if(!empty($this->update)) // If a table name to update was specified, this is an update statement...
		{
			$tmpQuerySelects = 'UPDATE';
		}
		elseif(!empty($this->insert)) // If a table name to insert into was specified, this is an insert statement...
		{
			$tmpQuerySelects = 'INSERT INTO';
		}
		elseif(empty($this->selects)) // Otherwise, if no selects are entered...
		{
			$tmpQuerySelects = 'SELECT *';
		}
		else // If selects *were* entered...
		{
			$tmpQuerySelects = []; // A new array to hold the collected SELECT segments...
			foreach($this->selects as $select)
			{
				if($select['field'] == '*') // If we want to select all fields from a particular table...
				{
					$tmpQuerySelects[] = $select['table'].'.*';
				}
				else // If we want to select a particular field...
				{
					$tmpQuerySelects[] = $select['table'].'.`'.$select['field'].'` AS '.$select['alias'];
				}
			}
			$tmpQuerySelects = 'SELECT '.implode(', ', $tmpQuerySelects);
		}
		
		/**
		 * Build tables/joins.
		 */
		if(!empty($this->update)) // If a table to UPDATE was specified...
		{
			$tmpQueryTables = '`'.$this->update.'`';
		}
		elseif(!empty($this->insert)) // If a table to INSERT INTO was specified...
		{
			$tmpQueryTables = '`'.$this->insert.'`';
		}
		else // Otherwise, build tables/joins for SELECT....
		{
			$tmpQueryTables = [];
			foreach($this->tables as $num => $table)
			{
				$tmpQueryTableSegment = '`'.$this->driver->config->database.'`.`'.$table['name'].'` AS '.$table['alias'];
				
				/**
				 * Except for the first table, add join conditions.
				 */
				if($num > 0)
				{
					if(!empty($table['conditions']))
					{
						$tmpConditions = [];
						foreach($table['conditions'] as $num2 => $cond)
						{
							$tmpConditions[] = $cond['field1'][0].'.`'.$cond['field1'][1].'` '.$cond['matchType'].' '.$cond['field2'][0].'.`'.$cond['field2'][1].'`';
						}
						$tmpConditions = 'ON '.implode(' AND ', $tmpConditions);
						$tmpQueryTableSegment .= ' '.$tmpConditions;
					}
				}
				
				$tmpQueryTables[] = $tmpQueryTableSegment;
			}
			$tmpQueryTables = 'FROM '.implode(' LEFT JOIN ', $tmpQueryTables);
		}
		
		/**
		 * Build SET clauses.
		 */
		$tmpQuerySets = '';
		if(!empty($this->sets))
		{
			$tmpQuerySets = [];
			foreach($this->sets as $fieldName => $value)
			{
				$tmpQuerySets[] = '`'.$fieldName.'` = '.$this->driver->pdo->quote($value);
			}
			$tmpQuerySets = 'SET '.implode(', ', $tmpQuerySets);
		}
		
		/**
		 * Build WHERE clauses.
		 */
		$tmpQueryWheres = '';
		if(!empty($this->wheres))
		{
			$tmpQueryWheres = 'WHERE '.$this->buildQueryWheres($this->wheres);
		}

		/**
		 * Build LIMIT clause.
		 */
		$tmpQueryLimit = '';
		if(
			empty($this->update) // If this is a SELECT query...
			and empty($this->insert) // If this is a SELECT query...
			and $this->quantity > 0 // And we're trying to retrieve a specific number of records...
		)
		{
			$startLimit = ( (int) $this->page - 1 ) * ( (int) $this->quantity ); // Start of the limit should be (pageNum - 1) * numRecords.
			$tmpQueryLimit = 'LIMIT '.$startLimit.', '.( (int) $this->quantity );
		}
		
		/**
		 * Assemble the completed query string.
		 */
		$query = $tmpQuerySelects.' '.$tmpQueryTables.' '.$tmpQuerySets.' '.$tmpQueryWheres.' '.$tmpQueryLimit.';';
		
		/**
		 * Put the built query into the appropriate property.
		 */
		$this->queryData = [ $query ];
	}
	
	/**
	 * Build a WHERE clause from an array. Handles subgrouping.
	 * 
	 * @param array $where An array of parameters to build a where clause.
	 * @param int $num A numeric counter for where in the current array level this element is.
	 * @return string The completed string for this where clause.
	 */
	private function buildQueryWheres($where, $num = 0)
	{
		if(!empty($where[0]) and is_array($where[0])) // If the first item in this condition is an array, this $where is actually a sub-group, and we should handle it differently...
		{
			$tmpQueryStrings = [];
			foreach($where as $subNum => $subWhere) // For each element in the sub-group...
			{
				$tmpQueryStrings[] = $this->buildQueryWheres($subWhere, $subNum); // Process the element, generate a string, and store it.
			}
			$whereString = '';
			if($num > 0) // If this is not the first item in the set, attach the condition type...
			{
				$whereString = $this->buildQueryWhereFirstConditionType($where).' ';
			}
			$whereString .= '('.implode(' ', $tmpQueryStrings).')'; // Implode the group of clauses, surround with parentheses, and prepend the condition type from the first item in the set (which will be presumed to apply to the set).
		}
		else // If the first item is not an array, assume we've actually reached a condition...
		{
			$whereString = '';
			if($num > 0) // If this is not the first item in the set, attach the condition type...
			{
				$whereString = $where['type'].' ';
			}
			
			/**
			 * Set match type for NULL.
			 */
			if(
				$where['matchType'] === '='
				and is_null($where['value'])
			)
			{
				$where['matchType'] = 'IS'; // Use the IS keyword, rather than =, because the expression ( anything = NULL ) always evaluates to null, never true. The expression (something IS NULL) can evaluate true.
			}
			/**
			 * Set match type for = against a group (IN keyword).
			 */
			elseif(
				$where['matchType'] === '='
				and is_array($where['value'])
				and array_filter($where['value'], 'is_int') == $where['value']
			)
			{
				$where['matchType'] = 'IN';
			}
			
			/**
			 * Set up alias and/or field name.
			 */
			if(!empty($this->update)) // If this is an update query, don't use aliases...
			{
				$fieldNameString = '`'.$where['name'].'`';
			}
			else
			{
				$fieldNameString = $where['alias'].'.`'.$where['name'].'`';
			}
			 
			/**
			 * Build the where string.
			 */
			$whereString .= $fieldNameString.' '.$where['matchType'].' '.$this->buildQueryWhereValue($where['value']); // Build string of the form t.`fieldName` = "value"
		}
		
		return $whereString;
	}
	
	/**
	 * Format a value for use in a MySQL query string.
	 * 
	 * @param mixed $value The value to be sanitized/formatted.
	 * @return string The string-formatted value for use in a MySQL query string.
	 */
	private function buildQueryWhereValue($value)
	{
		error_log('building query where value for '.print_r($value, true));
		
		if(is_null($value))
		{
			$value = 'NULL';
		}
		elseif($value === true)
		{
			$value = 'TRUE';
		}
		elseif($value === false)
		{
			$value = 'FALSE';
		}
		elseif(is_int($value))
		{
			// Do nothing.
		}
		elseif(
			is_array($value)
			and array_filter($value, 'is_int') == $value
		)
		{
			$value = '('.implode(', ', $value).')';
		}
		else // Otherwise, treat this as a string...
		{
			$value = $this->driver->safeString($value);
		}
		
		return $value;
	}
	
	/**
	 * Get the first (possibly nested) condition type (OR or AND) from the where condition.
	 * 
	 * This is necessary for sub-group support. Since the condition type that
	 * applies to the group is stored in the first *item* in the group, we must
	 * recursively search until we find an item. This could be nested more than one
	 * level deep.
	 * 
	 * @param [type] $where Parameter_Description
	 * @return [type] Return_Description
	 */
	private function buildQueryWhereFirstConditionType($where)
	{		
		if(!empty($where[0]) and is_array($where[0])) // If this is not a condition clause, but is a sub-group...
		{
			return $this->buildQueryWhereFirstConditionType($where[0]);
		}
		else
		{
			return $where['type'];
		}
	}
	
	/**
	 * Execute the query.
	 */
	public function execute()
	{
		$this->resultSet = $this->driver->rawQuery($this->queryData[0]); // Take the already-prepared query, and execute it, returning the ResultSet object.
	}
}
