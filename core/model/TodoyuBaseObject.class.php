<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSD License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/


/**
 * Add basic and lot used access functions for internal member vars
 *
 * @package		Todoyu
 * @subpackage	Core
 * @abstract
 */
class TodoyuBaseObject implements ArrayAccess {

	/**
	 * Record data (database row)
	 *
	 * @var	array
	 */
	protected $data = array();

	/**
	 * Cache for extra data, so they have to be fetched only once
	 *
	 * @var	array
	 */
	protected $cache = array();

	/**
	 * Table of the record
	 *
	 * @var	string
	 */
	protected $table;




	/**
	 * Initialize object. Only load data from database, when $idRecord is not zero
	 *
	 * @param	int		$idRecord
	 * @param	string		$table
	 */
	protected function __construct($idRecord, $table) {
		$idRecord	= (int) $idRecord;
		$this->table= trim(strtolower($table));

		if( $idRecord > 0 ) {
			$recordData	= Todoyu::db()->getRecord($table, $idRecord);
			if( is_array($recordData) ) {
				$this->data = $recordData;
			} else {
				TodoyuLogger::logError('Record not found! ID: "' . $idRecord . '", TABLE: "' . $table . '"');
//				die('<pre>'. print_r(debug_backtrace(false),true)) . '</pre>';
			}
		} else {
			//TodoyuLogger::logNotice('Record with ID 0 created (new object or missing data?) Table: ' . $table);
		}
	}



	/**
	 * Fallback for not defined getters. If a getter for a member variable is not defined,
	 * this function will be called and try to get the value from $this->data
	 * This is only for getters, so parameters are ignored
	 *
	 * @deprecated
	 * @notice	Define your own getters
	 * @param	string		$methodName
	 * @param	array		$params
	 * @return	string
	 */
	public function __call($methodName, $params) {
		$methodName	= strtolower($methodName);
		$dataKey	= str_replace('get', '', $methodName);

		if( substr($methodName, 0, 3) === 'get' && isset($this->data[$dataKey]) ) {
			return $this->get($dataKey);
		} else {
			TodoyuLogger::logNotice('Data "' . $dataKey . '" not found in ' . get_class($this) . ' (ID:' . $this->data['id'] . ')', $this->data);
			return '';
		}
	}



	/**
	 * Fallback for direct member access.
	 * First it checks for a getter function, if not available try to find the data in $this->data
	 *
	 * @deprecated
	 * @notice	Define your own getters
	 * @param	string		$memberName
	 * @return	string
	 */
	public function __get($memberName) {
		$dataKey	= strtolower($memberName);
		$methodName	= 'get' . $memberName;

		if( method_exists($this, $methodName) ) {
			return call_user_func(array($this, $methodName));
		} elseif( array_key_exists($dataKey, $this->data) ) {
			return $this->get($dataKey);
		} else {
			TodoyuLogger::logNotice('Data [' . $dataKey . '] not found in object [' . get_class($this) . ']');
			return '';
		}
	}



	/**
	 * Get record ID
	 *
	 * @return	int
	 */
	public function getID() {
		return $this->getInt('id');
	}



	/**
	 * Get data from internal record storage
	 *
	 * @param	string		$key
	 * @return	mixed
	 */
	public function get($key) {
		return $this->data[$key] ?? null;
	}



	/**
	 * Get data as integer
	 *
	 * @param	string		$fieldName
	 * @return	int
	 */
	public function getInt($fieldName) {
		return intval($this->get($fieldName));
	}



	/**
	 * Check whether a 'flag' field is set
	 * Flag fields are boolean fields (tinyint(1) with 0 or 1) in the database
	 *
	 * @param	string		$flagName
	 * @return	bool
	 */
	public function isFlagSet($flagName) {
		return $this->getInt($flagName) === 1;
	}



	/**
	 * Set a value
	 * Sets the value only in the object, this in not persistent
	 *
	 * @param	string		$key
	 * @param	mixed		$value
	 */
	public function set($key, $value) {
		$this->data[$key] = $value;
	}



	/**
	 * Check whether a property is set
	 *
	 * @param	string		$key
	 * @return	bool
	 */
	public function has($key) {
		return isset($this->data[$key]);
	}



	/**
	 * Update the object and the database
	 *
	 * @param	array	$data
	 */
	protected function update(array $data) {
			// Update database
		TodoyuRecordManager::updateRecord($this->table, $this->getID(), $data);
			// Update internal record
		$this->data = array_merge($this->data, $data);
			// Remove record query cache
		TodoyuRecordManager::removeRecordQueryCache($this->table, $this->getID());
	}



	/**
	 * Update a single field
	 *
	 * @param	string		$fieldName
	 * @param	mixed		$value			Scalar value
	 */
	protected function updateField($fieldName, $value) {
		$this->update(array(
			$fieldName	=> $value
		));
	}



	/**
	 * Check whether a property is not empty
	 *
	 * @param	string		$key
	 * @return	bool
	 */
	public function notEmpty($key) {
		return !empty($this->data[$key]);
	}



	/**
	 * Inject data.
	 * Useful if user initialized without an ID to avoid an extra request
	 *
	 * @param	array	$data
	 */
	public function injectData(array $data = array()) {
		$this->data = $data;
	}



	/**
	 * Check if current user is creator of the record
	 *
	 * @return	bool
	 */
	public function isCurrentPersonCreator() {
		return $this->getPersonCreateID() === Todoyu::personid();
	}



	/**
	 * Get data array
	 *
	 * @return	array
	 */
	public function getObjectData() {
		return $this->data;
	}



	/**
	 * Get date create
	 *
	 * @return	int
	 */
	public function getDateCreate() {
		return $this->getInt('date_create');
	}



	/**
	 * Get date update
	 *
	 * @return	int
	 */
	public function getDateUpdate() {
		return $this->getInt('date_update');
	}



	/**
	 * Check whether record was updated at least once
	 *
	 * @return	bool
	 */
	public function isUpdated() {
		return $this->getDateCreate() !== $this->getDateUpdate();
	}



	/**
	 * Get user ID of a specific type (create, update, assigned, etc)
	 *
	 * @param	string		$type
	 * @return	int
	 */
	public function getPersonID($type) {
		$dataKey = 'id_person_' . strtolower($type);

		return $this->getInt($dataKey);
	}



	/**
	 * Get user of a specific type (create, update, assigned, etc)
	 *
	 * @param	string		$type
	 * @return	TodoyuContactPerson
	 */
	public function getPerson($type = null) {
		$idPerson = $this->getPersonID($type);

		return TodoyuContactPersonManager::getPerson($idPerson);
	}



	/**
	 * Get ID of the creator person
	 *
	 * @return	int
	 */
	public function getPersonCreateID() {
		return $this->getPersonID('create');
	}



	/**
	 * Get the creator person
	 *
	 * @return	TodoyuContactPerson
	 */
	public function getPersonCreate() {
		return $this->getPerson('create');
	}



	/**
	 *
	 *
	 * @param	string	$key
	 * @return	bool
	 */
	protected function isInCache($key) {
		return isset($this->cache[$key]);
	}



	/**
	 * Get item from cache
	 *
	 * @param	string	$key
	 * @return	mixed
	 */
	protected function getCacheItem($key) {
		return $this->cache[$key];
	}



	/**
	 * Add item to cache
	 *
	 * @param	string	$key
	 * @param	mixed	$item
	 */
	protected function addToCache($key, $item) {
		$this->cache[$key] = $item;
	}



	/**
	 * Get data array for template rendering
	 *
	 * @return	array
	 */
	public function getTemplateData() {
		return $this->data;
	}



	/**
	 * Checks if the record is deleted
	 *
	 * @return	bool
	 */
	public function isDeleted() {
		return $this->getInt('deleted') === 1;
	}



	/**
	 * Called by empty() and isset() on member variables
	 *
	 * @magic
	 * @deprecated
	 * @param	string		$memberName
	 * @return	bool
	 */
	public function __isset($memberName) {
		return isset($this->data[$memberName]);
	}



	/**
	 * array access function to check if an attribute
	 * is set in the internal record storage
	 *
	 * Usage: $obj = new Obj(); isset($obj['id_person'])
	 *
	 * @magic
	 * @deprecated
	 * @param	mixed		$offset
	 * @return	bool
	 */
	public function offsetExists($offset) {
		return isset($this->data[$offset]);
	}



	/**
	 * array access function to delete an attribute
	 * in the internal record storage
	 *
	 * Usage: $obj = new Obj(); unset($obj['id_person'])
	 *
	 * @magic
	 * @deprecated
	 * @param	TKey		$offset
	 */
	public function offsetUnset($offset) {
		unset($this->data[$offset]);
	}



	/**
	 * array access function to set an attribute
	 * in the internal record storage
	 *
	 * Usage: $obj = new Obj(); $obj['id_person'] = 53;
	 *
	 * @magic
	 * @deprecated
	 * @param	TKey		$name
	 * @param	TValue		$value
	 */
	public function offsetSet($name, $value) {
		$this->data[$name] = $value;
	}



	/**
	 * array access function to get an attribute
	 * from the internal record storage
	 *
	 * Usage: $obj = new Obj(); echo $obj['id_person'];
	 *
	 * @magic
	 * @deprecated
	 * @param	string		$name
	 * @return	string
	 */
	public function offsetGet($name) {
		return $this->get($name);
	}



	/**
	 * Alias for getTemplateData
	 *
	 * @return	array
	 */
	public function getData() {
		return $this->getTemplateData();
	}
}

?>