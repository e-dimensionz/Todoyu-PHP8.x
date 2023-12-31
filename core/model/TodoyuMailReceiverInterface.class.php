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
 * Email receiver interface
 * All email receiver type classes have to implement this interface, so they can be called automatically
 *
 * @package		Todoyu
 * @subpackage	Core
 */
interface TodoyuMailReceiverInterface {

	/**
	 * Get the name of the receiver
	 *
	 * @return	string
	 */
	public function getName();



	/**
	 * Get the email address
	 *
	 * @return	string
	 */
	public function getAddress();



	/**
	 * Get the type
	 *
	 * @return	integer
	 */
	public function getType();



	/**
	 * Get record ID
	 *
	 * @return	integer
	 */
	public function getRecordID();



	/**
	 * Get tuple
	 * Combined type and record ID
	 *
	 * @return	string
	 */
	public function getTuple();



	/**
	 * Disable receiver
	 *
	 */
	public function disable();



	/**
	 * Enable receiver
	 *
	 */
	public function enable();



	/**
	 * Check whether receiver is disabled
	 *
	 * @return	boolean
	 */
	public function isDisabled();



	/**
	 * Check whether receiver is enabled
	 *
	 * @return	boolean
	 */
	public function isEnabled();



	/**
	 * Check whether receiver has a todoyu person
	 * In this case, the object should provide a getPerson() method which returns a TodoyuContactPerson
	 *
	 * @return	boolean
	 */
	public function hasPerson();



	/**
	 * Get receiver person if available in the object
	 * Use hasPerson() to check first!
	 *
	 * @return	TodoyuContactPerson|Boolean
	 */
	public function getPerson();



	/**
	 * Get label
	 *
	 * @param	boolean		$withAddress
	 * @return	string
	 */
	public function getLabel($withAddress = true);



	/**
	 * Get mail format
	 * "name@company.com" or "Name <name@company.com>"
	 *
	 * @return	string
	 */
	public function getMailFormat();
}

?>