<?php
/**
 * The data model result set template.
 */

namespace Acela\Core\Models;

use \Acela\Core as Core;

/**
 * The data model result set template.
 */
abstract class ResultSet implements \Countable, \Iterator
{
	/**
	 * @var Manager $manager The model manager.
	 */
	public $manager;
	
	/**
	 * @var \Acela\Core\Database\Drivers\ResultSet $databaseResultSet A database result set.
	 */
	public $databaseResultSet;
	
	/**
	 * @var array $results An array of result objects.
	 */
	protected $results = [];
	
	/**
	 * @var int $currentResultKey The current record number in the array of result objects.
	 */
	protected $currentResultKey = 0;

	/**
	 * Iterator Interface - Return the current result from the data set.
	 * 
	 * @return array The current result from the data set.
	 */
	public function current()
	{
		return $this->results[$this->currentResultKey];
	}

	/**
	 * Iterator Interface - Return the key of the current result from the data set.
	 * 
	 * @return array The key of the current result of the data set.
	 */
	public function key()
	{
		return $this->currentResultKey;
	}
	
	/**
	 * Iterator Interface - Move to the next element.
	 */
	public function next()
	{
		$this->currentResultKey++;
	}
	
	/**
	 * Iterator Interface - Rewind to the first element.
	 */
	public function rewind()
	{
		$this->currentResultKey = 0;
	}
	
	/**
	 * Iterator Interface - Determine if the current position is valid.
	 * 
	 * @return bool Indicates whether the current position is valid or not.
	 */
	public function valid()
	{
		if(!empty($this->results[$this->currentResultKey]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Countable Interface - Count the number of results in this result set.
	 * 
	 * @return int The number of results in the result set.
	 */
	public function count()
	{
		return count($this->results);
	}
	
	/**
	 * Load ->databaseResultSet into the ResultSet.
	 */
	public function loadResultSet()
	{
		foreach($this->databaseResultSet as $result)
		{
			$model = $this->createModel();
			
			/**
			 * Assign properties to the Model object.
			 */
			foreach($this->manager->databaseTableInfo['fields'] as $fieldName => $fieldInfo)
			{
				$model->{$fieldInfo['objectFieldName']} = $result[$fieldName]; // Assign the property.
			}
			
			/**
			 * Set the status of model we just loaded as the "original state".
			 */
			$model->setOriginalState();
			
			/**
			 * Add the completed model to the result set.
			 */
			$this->results[] = $model;
		}
	}
	
	/**
	 * Create a new model of the relevant type.
	 * 
	 * @return Model A new model.
	 */
	protected function createModel()
	{
		/**
		 * Get the appropriate class name for the model.
		 */
		$className = get_called_class(); // Should be something like \Acela\Core\Models\Generic\ResultSet
		$className = explode('\\', $className); // Explode into an array so we can grab "Generic".
		$className = __NAMESPACE__.'\\'.$className[count($className) - 2].'\Model'; // Should be something like \Acela\Core\Models\Generic\Model

		/**
		 * Create a new model instance.
		 */
		$model = new $className;
		$model->_manager = $this->manager;
		
		/**
		 * Return the new model instance.
		 */
		return $model;
	}
}
