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
 * Email receiver type: person
 *
 * @package		Todoyu
 * @subpackage	Contact
 */
class TodoyuContactMailReceiverPerson extends TodoyuMailReceiver {

	/**
	 * @var	TodoyuContactPerson		Person
	 */
	protected $person;



	/**
	 * Construct object
	 *
	 * @param	integer		$idPerson
	 */
	public function __construct($idPerson) {
		parent::__construct('contactperson', $idPerson);

		$this->person	= TodoyuContactPersonManager::getPerson($idPerson);
	}



	/**
	 * Get name
	 *
	 * @return	string
	 */
	public function getName() {
		return $this->person->getFullName();
	}



	/**
	 * Get email address
	 *
	 * @return	string|Boolean
	 */
	public function getAddress() {
		return $this->person->getEmail();
	}



	/**
	 * Get person ID
	 *
	 * @return	integer
	 */
	public function getPersonID() {
		return $this->getRecordID();
	}



	/**
	 * Check whether person is available
	 *
	 * @return	boolean
	 */
	public function hasPerson() {
		return true;
	}



	/**
	 * Get person
	 *
	 * @return	TodoyuContactPerson
	 */
	public function getPerson() {
		return TodoyuContactPersonManager::getPerson($this->getPersonID());
	}

}

?>