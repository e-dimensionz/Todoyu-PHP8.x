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
 * Sysmanager preferences
 *
 * @package		Todoyu
 * @subpackage	Sysmanager
 */
class TodoyuSysmanagerPreferences {

	/**
	 * Save a preference for sysmanager
	 *
	 * @param	string		$preference
	 * @param	string		$value
	 * @param	integer		$idItem
	 * @param	boolean		$unique
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 */
	public static function savePref($preference, $value, $idItem = 0, $unique = false, $idArea = 0, $idPerson = 0) {
		TodoyuPreferenceManager::savePreference(EXTID_SYSMANAGER, $preference, $value, $idItem, $unique, $idArea, $idPerson);
	}



	/**
	 * Get a sysmanager preference
	 *
	 * @param	string		$preference
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	boolean		$unserialize
	 * @param	integer		$idPerson
	 * @return	string
	 */
	public static function getPref($preference, $idItem = 0, $idArea = 0, $unserialize = false, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreference(EXTID_SYSMANAGER, $preference, $idItem, $idArea, $unserialize, $idPerson);
	}



	/**
	 * Get sysmanager preferences
	 *
	 * @param	string		$preference
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 * @return	Array
	 */
	public static function getPrefs($preference, $idItem = 0, $idArea = 0, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreferences(EXTID_SYSMANAGER, $preference, $idItem, $idArea, $idPerson);
	}



	/**
	 * Delete sysmanager preference
	 *
	 * @param	string		$preference
	 * @param	string		$value
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 */
	public static function deletePref($preference, $value = null, $idItem = 0, $idArea = 0, $idPerson = 0) {
		TodoyuPreferenceManager::deletePreference(EXTID_SYSMANAGER, $preference, $value, $idItem, $idArea, $idPerson);
	}



	/**
	 * Save currently active sysmanager area module
	 *
	 * @param	string	$module
	 */
	public static function saveActiveModule($module) {
		self::savePref('module', $module, 0, true);
	}



	/**
	 * Get previously active sysmanager area module
	 *
	 * @return	string
	 */
	public static function getActiveModule() {
		return self::getPref('module');
	}



	/**
	 * Get currently active sysmanager tab
	 *
	 * @param	string		$type
	 * @return	string
	 */
	public static function getActiveTab($type) {
		return self::getPref($type . '-tab');
	}



	/**
	 * Save active tab preference
	 *
	 * @param	string		$type
	 * @param	string		$tab
	 */
	public static function saveActiveTab($type, $tab) {
		self::savePref($type . '-tab', $tab, 0, true);
	}



	/**
	 * Save given extension's rights
	 *
	 * @param	string	$ext
	 */
	public static function saveRightsExt($ext) {
		self::savePref('rights-ext', $ext, 0, true);
	}



	/**
	 * Get sysmanager rights settings
	 *
	 * @return	string
	 */
	public static function getRightsExt() {
		$ext	= self::getPref('rights-ext');

		if( !$ext ) {
//			$extKeys= TodoyuExtensions::getInstalledExtKeys();
			$ext	= $ext[0];
		}

		return $ext;
	}



	/**
	 * Save rights and roles to prefs
	 *
	 * @param	array	$roles
	 */
	public static function saveRightsRoles(array $roles) {
		$roles		= TodoyuArray::intval($roles, true, true);
		$roleList	= implode(',', $roles);

		TodoyuRightsManager::saveChangeTime();
		self::savePref('rights-roles', $roleList, 0, true);
	}



	/**
	 * Get rights and roles from prefs
	 *
	 * @return	integer[]
	 */
	public static function getRightsRoles() {
		$roleList	= self::getPref('rights-roles');

		return TodoyuArray::intExplode(',', $roleList);
	}

}

?>