<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2013, snowflake productions GmbH, Switzerland
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
 * Contact information object
 *
 * @package		Todoyu
 * @subpackage	Contact
 */
class TodoyuContactContactInfo extends TodoyuBaseObject {

	/**
	 * constructor of the class
	 *
	 * @param	integer		$idContactInfo
	 */
	function __construct($idContactInfo) {
		parent::__construct($idContactInfo, 'ext_contact_contactinfo');
	}



	/**
	 * Get info
	 *
	 * @return	string
	 */
	public function getInfo() {
		return $this->get('info');
	}



	/**
	 * Check whether contact info is preferred
	 *
	 * @return	boolean
	 */
	public function isPreferred() {
		return $this->isFlagSet('is_preferred');
	}


	/**
	 * Get contact info type ID
	 *
	 * @return	integer
	 */
	public function getContactInfoTypeID() {
		return $this->getInt('id_contactinfotype');
	}



	/**
	 * Get contact info type
	 *
	 * @return	TodoyuContactContactInfoType
	 */
	public function getContactInfoType() {
		return TodoyuContactContactInfoTypeManager::getContactInfoType($this->getContactInfoTypeID());
	}



	/**
	 * Get type title
	 *
	 * @return	string
	 */
	function getTypeLabel() {
		return $this->getContactInfoType()->getTitle();
	}
}

?>