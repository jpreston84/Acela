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
	private function buildQuery()
	{
		return $query;
	}
}