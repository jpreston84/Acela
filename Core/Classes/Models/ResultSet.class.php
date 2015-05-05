<?php
/**
 * The data model result set template.
 */

namespace Acela\Core\Models;

use \Acela\Core as Core;

/**
 * The data model result set template.
 */
abstract class ResultSet
{
	/**
	 * @var \Acela\Core\Database\Drivers\ResultSet $databaseResultSet A database result set.
	 */
	public $databaseResultSet;
	
	/**
	 * @var array $results An array of result objects.
	 */
	protected $results = [];

	/**
	 * Constuctor - Build the ResultSet from the database ResultSet.
	 * 
	 * @param \Acela\Core\Database\Drivers\ResultSet $resultSet A database result set.
	 */
	public function __construct($resultSet)
	{
		/**
		 * Load data from the result set.
		 */
		$this->databaseResultSet = $resultSet;
		$this->loadResultSet();
	}
	
	/**
	 * Load ->databaseResultSet into the ResultSet.
	 */
	protected function loadResultSet()
	{
		foreach($this->databaseResultSet as $result)
		{
			$modelClass = __NAMESPACE__.'\Model';
			$model = new $modelClass;
			
			// Assign properties to model object.
			
			$this->results[] = $model;
		}
	}
}