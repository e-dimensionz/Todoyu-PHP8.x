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
 * Profile preference manager
 *
 * @package		Todoyu
 * @subpackage	Profile
 */
class TodoyuProfilePreferences {

	/**
	 * Save a preference for profile
	 *
	 * @param	string		$preference
	 * @param	string		$value
	 * @param	integer		$idItem
	 * @param	boolean		$unique
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 */
	public static function savePref($preference, $value, $idItem = 0, $unique = false, $idArea = 0, $idPerson = 0) {
		TodoyuPreferenceManager::savePreference(EXTID_PROFILE, $preference, $value, $idItem, $unique, $idArea, $idPerson);
	}



	/**
	 * Get a profile preference
	 *
	 * @param	string		$preference
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	boolean		$unserialize
	 * @param	integer		$idPerson
	 * @return	string
	 */
	public static function getPref($preference, $idItem = 0, $idArea = 0, $unserialize = false, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreference(EXTID_PROFILE, $preference, $idItem, $idArea, $unserialize, $idPerson);
	}



	/**
	 * Get profile preference
	 *
	 * @param	string		$preference
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 * @return	Array
	 */
	public static function getPrefs($preference, $idItem = 0, $idArea = 0, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreferences(EXTID_PROFILE, $preference, $idItem, $idArea, $idPerson);
	}



	/**
	 * Delete profile preference
	 *
	 * @param	string		$preference
	 * @param	string		$value
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 */
	public static function deletePref($preference, $value = null, $idItem = 0, $idArea = 0, $idPerson = 0) {
		TodoyuPreferenceManager::deletePreference(EXTID_PROFILE, $preference, $value, $idItem, $idArea, $idPerson);
	}



	/**
	 * Get key of currently active profile module
	 *
	 * @return	string
	 */
	public static function getActiveModule() {
		$module	= self::getPref('module');

		if( !$module ) {
			$module = 'general';
		}

		return $module;
	}



	/**
	 * Save preference: profile's currently active module
	 *
	 * @param	string	$module
	 */
	public static function saveActiveModule($module) {
		self::savePref('module', $module, 0, true);
	}



	/**
	 * Get profile general tab preference
	 *
	 * @return	string
	 */
	public static function getGeneralTab() {
		$tab	= self::getPref('tab-general');

		if( !$tab ) {
			$tab = 'main';
		}

		return $tab;
	}



	/**
	 * Save profile preference: general tab
	 *
	 * @param	string	$tab
	 */
	public static function saveGeneralTab($tab) {
		self::savePref('tab-general', $tab, 0, true);
	}

}

?>