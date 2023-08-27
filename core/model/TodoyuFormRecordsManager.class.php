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
 * Manage form element records selector
 *
 * @package		Todoyu
 * @subpackage	Core
 */
class TodoyuFormRecordsManager {

	/**
	 * @var	String[]		List of records source callbacks
	 */
	protected static $listCallback = array();



	/**
	 * Add a records type form element
	 *
	 * @param	string		$type
	 * @param	string		$formElementClass
	 * @param	string		$listCallback
	 */
	public static function addType($type, $formElementClass, $listCallback) {
		self::$listCallback[$type] = $listCallback;

		TodoyuFormManager::addFieldTypeRecords($type, $formElementClass);
	}



	/**
	 * Get list callback function
	 *
	 * @param	string		$type
	 * @return	string
	 */
	protected static function getListCallback($type) {
		return self::$listCallback[$type];
	}



	/**
	 *
	 * @param	string		$type
	 * @param	string[]	$searchWords
	 * @param	string[]	$ignoreKeys
	 * @param	array		$params
	 * @return	Array[]
	 */
	public static function getListItems($type, array $searchWords, array $ignoreKeys = array(), array $params = array()) {
		TodoyuExtensions::loadAllForm();

		$callback	= self::getListCallback($type);
		$searchWords= TodoyuArray::trim($searchWords, true);
		$ignoreKeys	= TodoyuArray::trim($ignoreKeys, true);

		if( TodoyuFunction::isFunctionReference($callback) ) {
			return TodoyuFunction::callUserFunction($callback, $searchWords, $ignoreKeys, $params, $type);
		} else {
			TodoyuLogger::logError('Invalid records type. No listing callback found for <' . $type . '>');
			return array();
		}
	}

}

?>