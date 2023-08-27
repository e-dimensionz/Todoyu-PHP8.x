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
 * Extension update callback base class
 *
 * @package		Todoyu
 * @subpackage	Core
 * @abstract
 */
abstract class TodoyuExtensionSetup {

	/**
	 * Execute database updates from update files in extension
	 *
	 * @param	string		$extKey
	 * @param	string		$previousVersion
	 * @param	string		$currentVersion
	 */
	protected static function runDbUpdateFiles($extKey, $previousVersion, $currentVersion) {
		$path	= TodoyuExtensions::getExtPath($extKey, 'config/db/update');
		$files	= TodoyuFileManager::getVersionFiles($path, 'sql', $previousVersion, $currentVersion);

		foreach($files as $file) {
			TodoyuSQLManager::executeQueriesFromFile($path . '/' . $file);
		}
	}



	/**
	 * Callback: Other extension has been uninstalled
	 *
	 * @param	string		$extKey
	 * @param	string		$otherExtKey
	 */
	public static function afterOtherExtensionUninstall($extKey, $otherExtKey) {


	}



	/**
	 * Callback: Other extension has been installed
	 *
	 * @param	string		$extKey
	 * @param	string		$otherExtKey
	 */
	public static function afterOtherExtensionInstall($extKey, $otherExtKey) {


	}



	/**
	 * Callback: Before database is updated with changes from table.sql
	 *
	 * @param	string		$extKey
	 * @param	string		$previousVersion
	 * @param	string		$currentVersion
	 */
	public static function beforeDbUpdate($extKey, $previousVersion, $currentVersion) {
		self::runDbUpdateFiles($extKey, $previousVersion, $currentVersion);
	}



	/**
	 * Callback: Before extension update
	 *
	 * @param	string		$extKey
	 */
	public static function beforeUpdate($extKey) {

	}



	/**
	 * Callback: After extension installation
	 *
	 * @param	string		$extKey
	 */
	public static function afterInstall($extKey) {

	}



	/**
	 * Callback: Before extension un-installation
	 *
	 * @param	string		$extKey
	 */
	public static function beforeUninstall($extKey) {

	}



	/**
	 * Callback: After extension update
	 *
	 * @param	string		$extKey
	 * @param	string		$previousVersion
	 * @param	string		$currentVersion
	 */
	public static function afterUpdate($extKey, $previousVersion, $currentVersion) {

	}



	/**
	 * Callback: Before a major version update
	 *
	 * @param	string		$extKey
	 * @param	string		$previousVersion
	 * @param	string		$currentVersion
	 */
	public static function beforeMajorUpdate($extKey, $previousVersion, $currentVersion) {

	}



	/**
	 * Callback: After a major version update
	 * @param	string		$extKey
	 * @param	string		$previousVersion
	 * @param	string		$currentVersion
	 */
	public static function afterMajorUpdate($extKey, $previousVersion, $currentVersion) {

	}

}

?>