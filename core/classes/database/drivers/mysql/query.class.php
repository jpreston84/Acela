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
	public function buildQuery()
	{
		/**
		 * Build SELECT segment.
		 */
		if(empty($this->selects)) // If no selects are entered...
		{
			$tmpQuerySelects = 'SELECT *';
		}
		else
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
					foreach($table['conditions'] as $cond)
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
		
		/**
		 * Build WHERE clauses.
		 */
		$tmpQueryWheres = '';
		if(!empty($this->wheres))
		{
			$tmpQueryWheres = 'WHERE '.implode('', $this->buildQueryWheres($this->wheres));
		}
		
		print_r($this->wheres);
		
		$query = $tmpQuerySelects.' '.$tmpQueryTables.' '.$tmpQueryWheres;
		
		return $query;
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
		if(!empty($where[0]) and is_array($where[0])) // If the first item in this condition is an array, it's a sub-group, and we should handle it differently...
		{
			$tmpQueryStrings = [];
			foreach($where as $num => $subWhere) // For each element in the sub-group...
			{
				$tmpQueryStrings[] = $this->buildQueryWhereFirstConditionType($subWhere).' '.$this->buildQueryWheres($subWhere, $num); // Process the element, generate a string, and store it.
			}
			$whereString = '('.implode(' ', $tmpQueryStrings).')'; // Implode the group of clauses, surround with parentheses, and prepend the condition type from the first item in the set (which will be presumed to apply to the set).
		}
		else // If the first item is not an array, assume we've actually reached a condition...
		{
			$whereString = '';
			if($num == 0) // If this is not the first item in the set, attach the condition type...
			{
				$whereString = $where['type'].' ';
			}
			$whereString .= $where['alias'].'.`'.$where['name'].'` '.$where['matchType'].' '.$this->buildQueryWhereValue($where['value']); // Build string of the form t.`fieldName` = "value"
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
		else // Otherwise, treat this as a string...
		{
			$value = '"'.$this->driver->safeString($value).'"';
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
	
	public function executeQuery(array $queryData)
	{
		
	}
}