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
 * Role object for rights management
 *
 * @package		Todoyu
 * @subpackage	Core
 */
class TodoyuRole extends TodoyuBaseObject {

	/**
	 * Initialize
	 *
	 * @param	integer		$idRole
	 */
	public function __construct($idRole) {
		parent::__construct($idRole, 'system_role');
	}



	/**
	 * Get title
	 *
	 * @return	string
	 */
	public function getTitle() {
		return $this->get('title');
	}



	/**
	 * Check whether role is active
	 *
	 * @return	boolean
	 */
	public function isActive() {
		return ((int) $this->get('is_active')) === 1;
	}



	/**
	 * Get role description
	 *
	 * @return	string
	 */
	public function getDescription() {
		return $this->get('description');
	}



	/**
	 * Get person IDs
	 *
	 * @return	Array
	 */
	public function getPersonIDs() {
		return TodoyuRoleManager::getPersonIDs($this->getID());
	}



	/**
	 * Get persons with this role
	 *
	 * @return	Array
	 */
	public function getPersons() {
		return TodoyuRoleManager::getPersonData($this->getID());
	}



	/**
	 * Get number of group users
	 *
	 * @return	integer
	 */
	public function getNumPersons() {
		return TodoyuRoleManager::getNumPersons($this->getID());
	}



	/**
	 * Check if group has any users
	 *
	 * @return	boolean
	 */
	public function hasPersons() {
		return sizeof($this->getNumPersons()) > 0;
	}



	/**
	 * Load foreign role data
	 */
	private function loadForeignData()  {
		$this->data['persons']	= TodoyuRoleManager::getPersonData($this->getID());
	}



	/**
	 * Get templating data
	 *
	 * @param	boolean		$loadForeignData
	 * @return	Array
	 */
	public function getTemplateData($loadForeignData = false) {
		if( $loadForeignData ) {
			$this->loadForeignData();
		}

		return parent::getTemplateData();
	}

}

?>