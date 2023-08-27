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
 * timetracking preferences
 *
 * @package		Todoyu
 * @subpackage	Timetracking
 */
class TodoyuTimetrackingPreferences {

	/**
	 * Save a preference for project
	 *
	 * @param	string		$preference
	 * @param	string		$value
	 * @param	integer		$idItem
	 * @param	boolean		$unique
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 */
	public static function savePref($preference, $value, $idItem = 0, $unique = false, $idArea = 0, $idPerson = 0) {
		TodoyuPreferenceManager::savePreference(EXTID_TIMETRACKING, $preference, $value, $idItem, $unique, $idArea, $idPerson);
	}



	/**
	 * Get a preference
	 *
	 * @param	string		$preference
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	boolean		$unserialize
	 * @param	integer		$idPerson
	 * @return	string
	 */
	public static function getPref($preference, $idItem = 0, $idArea = 0, $unserialize = false, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreference(EXTID_TIMETRACKING, $preference, $idItem, $idArea, $unserialize, $idPerson);
	}



	/**
	 * Get  project preference
	 *
	 * @param	string		$preference
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 * @return	Array
	 */
	public static function getPrefs($preference, $idItem = 0, $idArea = 0, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreferences(EXTID_TIMETRACKING, $preference, $idItem, $idArea, $idPerson);
	}



	/**
	 * Delete project preference
	 *
	 * @param	string		$preference
	 * @param	string		$value
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 */
	public static function deletePref($preference, $value = null, $idItem = 0, $idArea = 0, $idPerson = 0) {
		TodoyuPreferenceManager::deletePreference(EXTID_TIMETRACKING, $preference, $value, $idItem, $idArea, $idPerson);
	}

}

?>