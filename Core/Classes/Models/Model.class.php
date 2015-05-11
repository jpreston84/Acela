<?php
/**
 *  The data model template.
 */

namespace Acela\Core\Models;

use \Acela\Core as Core;

/**
 *  The data model template.
 */
abstract class Model
{
	/**
	 * @var Manager $_manager Manager for this model.
	 */
	public $_manager; // It is necessary to use $_manager rather than $manager to avoid having manager as a reserved property not usable in database tables.
	
	/**
	 * @var bool $_altered Has this object been altered or not since it was loaded?
	 */
	public $_altered = false;
	
	/**
	 * @var bool $_new Is this a new record or an existing one?
	 */
	public $_new = false;

	/**
	 * @var bool $_backup Is this a backup copy of some other object?
	 */
	public $_backup = false;
	
	/**
	 * @var array $_properties All the _properties of this model.
	 */
	protected $_properties = [];
	
	/**
	 * @var array $_originalProperties The original properties of this object when it was created, for backup purposes.
	 */
	protected $_originalProperties = [];
	
	/**
	 * Magic Method - Get the value of a property of this object.
	 * 
	 * @param string $name The name of the property to get.
	 * @return mixed The value of the property.
	 */
	public function __get($name)
	{
		if(isset($this->_properties[$name]))
		{
			return $this->_properties[$name];
		}
		else
		{
	        $trace = debug_backtrace();
			trigger_error(
				'Undefined property via __get(): ' . $name .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_NOTICE
			);
			return null;
		}
	}

	/**
	 * Magic Method - Set a property of this object.
	 * 
	 * @param string $name The name of the property to set.
	 * @param mixed $value The value of the property.
	 */
	public function __set($name, $value)
	{
		$this->_properties[$name] = $value; // Set the property.
		$this->_altered = true; // The object has been altered.
	}
	
	/**
	 * Magic Method - Call a method on this object.
	 * 
	 * This should be used exclusively for the wildcard ->getFirst*() and
	 * ->getAll*() methods.
	 * 
	 * @param string $name The name of the method that was called.
	 * @param array $arguments Arguments to pass to the method.
	 * @return mixed The output of the method call.
	 */
	public function __call($name, $arguments)
	{
		/**
		 * Handle methods like ->getFirstUser() for loading associated Models.
		 */
		if(substr($name, 0, 8) === 'getFirst')
		{
			/**
			 * Get $params if provided.
			 */
			$params = [];
			if(!empty($arguments))
			{
				$params = $arguments[0];
			}
			return $this->getFirstLinkedObject(substr($name, 8), $params);
		}
		/**
		 * Handle methods like ->getAllUsers() for loading associated Models.
		 */
		elseif(substr($name, 0, 6) === 'getAll')
		{
			/**
			 * Get $params if provided.
			 */
			$params = [];
			if(!empty($arguments))
			{
				$params = $arguments[0];
			}
			return $this->getFirstLinkedObject(Core\wordSingularize(substr($name, 6)), $params);
		}
		else
		{
			error_log(print_r(debug_backtrace(), true));
			die('Uncaught error.');
		}
	}

	/**
	 * Make the "original state" of the object match the current state.
	 * 
	 * This will turn off the ->_altered flag and copy all properties to
	 * ->_originalProperties.
	 */
	public function setOriginalState()
	{
		$this->_altered = false;
		$this->_originalProperties = $this->_properties;
	}
	
	/**
	 * Save the current model.
	 */
	public function save()
	{
		/**
		 * If the object hasn't been altered, don't save.
		 */
		if(!$this->_altered)
		{
			return;
		}
		
		/**
		 * Save a backup copy before any changes have been made, if applicable.
		 */
		if(!$this->_backup) // If this object is not itself a backup copy...
		{
			$this->saveBackupVersion(); // Try to save a backup copy.
		}
		
		/**
		 * Update the createdOn/modifiedOn times and users.
		 */
		if(!$this->_backup) // If this is not a backup copy...
		{
			$this->setTimestamps(); // Update timestamps for this record.
		}
		
		/**
		 * Create query object.
		 */
		$query = $GLOBALS['core']->db->query();
		
		/**
		 * Determine UPDATE or INSERT.
		 */
		if($this->_new)
		{
			$query->insert($this->_manager->databaseTableName);
		}
		else
		{
			$query->update($this->_manager->databaseTableName);
		}
		
		foreach($this->_properties as $property => $value) // For every property in this instance...
		{
			$databaseFieldName = $this->_manager->getDatabaseFieldName($property);
			
			/**
			 * Exclude primary key property.
			 */
			if($this->_manager->databaseTableInfo['fields'][$databaseFieldName]['primary'])
			{
				continue; // Skip this property and move on to the next.
			}
			
			/**
			 * Add this field to the query.
			 */
			$query->set($databaseFieldName, $value);
		}
		
		/**
		 * Add WHERE clause based on primary key.
		 */
		if(!$this->_new) // If this is not a new record...
		{
			foreach($this->_properties as $property => $value) // For every property in this instance...
			{
				$databaseFieldName = $this->_manager->getDatabaseFieldName($property);
				if($this->_manager->databaseTableInfo['fields'][$databaseFieldName]['primary']) // If this is the primary key for the table...
				{
					$query->where(null, $databaseFieldName, '=', $value);
				}
			}
		}
		
		/**
		 * Run the INSERT/UPDATE query.
		 */
		$query->run();
		
		/**
		 * Get insert ID and add it to primary key field.
		 */
		if($this->_new) // If this was a new entry...
		{
			$lastInsertId = $GLOBALS['core']->db->getLastInsertId();
			foreach($this->_properties as $property => $value) // For every property in this instance...
			{
				$databaseFieldName = $this->_manager->getDatabaseFieldName($property);
				if($this->_manager->databaseTableInfo['fields'][$databaseFieldName]['primary']) // If this is the primary key for the table...
				{
					$this->_properties[$property] = $lastInsertId;
				}
			}
		}

		/**
		 * Change parameters to reflect that we've saved changes.
		 */
		$this->_new = false;
		$this->_altered = false;
		
		return;
	}
	
	/**
	 * Update the createdOn/modifiedOn datetime fields and users.
	 * 
	 * This function should only be called by ->save().
	 */
	protected function setTimestamps()
	{
		/**
		 * Update createdOn time.
		 */
		if(
			array_key_exists('createdOn', $this->_properties) // If a createdOn field exists...
			and $this->_new // And this is a new record that hasn't been saved yet...
		)
		{
			$this->_properties['createdOn'] = date('Y-m-d H:i:s'); // Set the creation datetime to the current datetime.
		}
		
		/**
		 * Update modifiedOn time.
		 */
		if(
			array_key_exists('modifiedOn', $this->_properties) // If a modifiedOn field exists...
		)
		{
			$this->_properties['modifiedOn'] = date('Y-m-d H:i:s'); // Set the modification datetime to the current datetime.
		}
		
		/**
		 * Update createdBy user.
		 */
		if(
			array_key_exists('createdBy', $this->_properties) // If a createdBy field exists...
			and $this->_new // And this is a new record that hasn't been saved yet...
		)
		{
			$this->_properties['createdBy'] = Core\User::getInstance()->id;
		}

		/**
		 * Update modifiedBy user.
		 */
		if(
			array_key_exists('modifiedBy', $this->_properties) // If a modifiedBy field exists...
		)
		{
			$this->_properties['modifiedBy'] = Core\User::getInstance()->id;
		}
	}
	
	/**
	 * Save a backup copy of the current object.
	 */
	public function saveBackupVersion()
	{
		/**
		 * If this *is* a backup version, do not continue.
		 */
		if($this->_backup)
		{
			return;
		}
		
		/**
		 * Determine the table and object names of the backup.
		 */
		$modelName = $this->_manager->modelName.'Version';
		$tableName = Core\wordPluralize($modelName);
		$tableName[0] = strtolower($tableName[0]);

		/**
		 * If backup version table does not exist, skip backup creation.
		 */
		if(!$GLOBALS['core']->db->tableExists($tableName))
		{
			return;
		}
		
		$versionManager = Core\Model::getInstance($modelName); // Get the manager for the appropriate version object, possibly creating one from scratch if need be.

		/**
		 * Create a new backup version model object.
		 */
		$backup = $versionManager->create(); // Create a new instance of the backup version model.
		$backup->_backup = true; // This is a backup version.
		
		/**
		 * Copy all properties from original state into the backup.
		 */
		foreach($this->_originalProperties as $property => $value)
		{
			/**
			 * Since this is an object of a different type, convert property name to
			 * the database field name.
			 */
			$property = $this->_manager->getDatabaseFieldName($property);
			
			/**
			 * Add the property to the backup object.
			 */
			$backup->$property = $value;
		}
		
		/**
		 * Save the backup object.
		 */
		$backup->save();
	}

	/**
	 * Get the first linked object of the specified type.
	 * 
	 * @param string $modelName The name of the model type to find links to.
	 * @param array $params Additional search parameters to use.
	 * @return Model The first matching result.
	 */
	public function getFirstLinkedObject($modelName, $params = [])
	{
		$resultSet = $this->getLinkedObjects($modelName, $params, 1);
		foreach($resultSet as $result)
		{
			return $result;
		}
	}
		
	/**
	 * Get linked objects of the specified type.
	 * 
	 * @param string $modelName The name of the model type to find links to.
	 * @param array $params Additional search parameters to use.
	 * @param int $qty The number of objects to returned.
	 * @return ResultSet A set of results.
	 */
	public function getLinkedObjects($modelName, $params = [], $qty = 0)
	{
		/**
		 * Find the kind of link that exists.
		 */
		$linkType = Core\Model::findLink($this->_manager->modelName, $modelName);
		
		/**
		 * If no link, return false.
		 */
		if($linkType === false)
		{
			return false;
		}
		/**
		 * If the link is in this model, generate the query based on the lookup field in
		 * this model's table.
		 */
		elseif($linkType === 1)
		{
			/**
			 * Get an instance of the foreign object manager.
			 */
			$manager = Core\Model::getInstance($modelName);
			
			/**
			 * Get name of ID field from foreign manager to use as foreign key in this
			 * table.
			 */
			$idField = $manager->getPrimaryKey();
			
			/**
			 * Identify the name of the foreign key field in this object.
			 */
			$foreignKeyField = $this->_manager->getObjectFieldName($manager->getDatabaseFieldName($idField));
			
			/**
			 * Retrieve records that match.
			 */
			$params[$idField] = $this->$foreignKeyField; // Get the value of the foreign key field, and set it as the ID field to match.
			$resultSet = $manager->get($params, $qty);
			
			/**
			 * Return the records.
			 */
			return $resultSet;
		}
		/**
		 * If the link is in the foreign model, do the inverse of above.
		 */
		elseif($linkType === -1)
		{
			/**
			 * Get an instance of the foreign object manager.
			 */
			$manager = Core\Model::getInstance($modelName);
			
			/**
			 * Get name of ID field from the current manager to use as foreign key.
			 */
			$idField = $this->_manager->getPrimaryKey();
			
			/**
			 * Identify the name of the foreign key field in the foreign object, which will
			 * point specifically to the current object.
			 */
			$foreignKeyField = $manager->getObjectFieldName($this->_manager->getDatabaseFieldName($idField));
			
			/**
			 * Retrieve records that match.
			 */
			$params[$foreignKeyField] = $this->$idField;
			$resultSet = $manager->get($params, $qty);
			
			/**
			 * Return the records.
			 */
			return $resultSet;
		}
		/**
		 * If there is a pivot table, get pivot records, then get actual results.
		 */
		elseif($linkType === 0)
		{
			/**
			 * Get an instance of the foreign object manager.
			 */
			$manager = Core\Model::getInstance($modelName);
			
			/**
			 * Get the pivot model name.
			 */
			$pivotModelName = Core\Model::getPivotModelName($this->_manager->modelName, $manager->modelName);

			/**
			 * Set up parameters.
			 */
			$pivotParams = []; // Set up a separate set of pivot parameters, so as not to overwrite the $params provided to the function.
			$pivotParams[$this->_manager->getDatabaseFieldName($this->_manager->getPrimaryKey())] = $this->{$this->_manager->getPrimaryKey()}; // Get the database field name for the primary key of the current manager, and set the search parameter to the current object's primary key value.
			
			/**
			 * Get an instance of the pivot table manager, and retrieve the pivot records.
			 */
			$pivotManager = Core\Model::getInstance($pivotModelName);
			$resultSet = $pivotManager->get($pivotParams, 0);
			
			/**
			 * Iterate through each pivot record, and build an array of IDs to
			 * look for in the foreign model.
			 */
			$foreignModelIds = [];
			$foreignModelKeyPivotFieldName = $pivotManager->getObjectFieldName($model->getDatabaseFieldName($model->getPrimaryKey())); // Get the name of the database field for the primary key in the foreign model, then convert it to the object field name that will be used in the pivot model.
			foreach($resultSet as $result) // For each pivot record...
			{
				$foreignModelIds[] = $result->$foreignModelKeyPivotFieldName; // Get the value from the pivot record that corresponds to a primary key value in the foreign manager, which is related to the current object.
			}
			
			/**
			 * Retrieve records that match.
			 */
			$params[$model->getPrimaryKey()] = $foreignModelIds;
			$resultSet = $manager->get($params, $qty);
			
			/**
			 * Return the records.
			 */
			return $resultSet;
		}
	}
}
