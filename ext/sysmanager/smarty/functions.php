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
 * Sysmanager specific smarty plugins
 *
 * @package		Todoyu
 * @subpackage	Template
 */



/**
 * Checks whether current extension has records registered
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	string			$params
 * @return	integer
 */
function fn_extMgr_hasRecords($extKey) {
    $dt = TodoyuSysmanagerExtManager::getRecordTypes($extKey);
    return empty($dt) ? 0 : sizeof($dt);
}



/**
 * Checks whether extension has rights config registered
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	string			$extKey
 * @return	boolean
 */
function fn_extMgr_hasRighsConfig($extKey) {
	return TodoyuSysmanagerRightsEditorManager::hasRightsConfig($extKey);
}



/**
 * Checks whether extension has something to configure
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	string			$extKey
 * @return	boolean
 */
function fn_extMgr_hasConfig($extKey) {
	return TodoyuSysmanagerExtManager::extensionHasConfig($extKey);
}



/**
 * Checks whether extension has informations registered
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	string			$extKey
 * @return	boolean
 */
function fn_extMgr_hasExtInfo($extKey) {
	return TodoyuSysmanagerExtManager::getExtInfos($extKey) !== false;
}


/**
 * @param	string			$extKey
 * @return	boolean
 */
function fn_extMgr_isSysExt($extKey) {
	return TodoyuSysmanagerExtManager::isSysExt($extKey);
}



/**
 * Render extension icon image tag (if exists)
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params
 * @return	string
 */
function smarty_compiler_extIcon($params) {
	return "'<img src=\"ext/' . " . $params['extKey'] . " . '/asset/img/exticon.png\" width=\"16\" height=\"16\" />'";
}



/**
 * Convert the state key into an state image
 *
 * @param	array		$params
 * @return	string
 */
function smarty_function_ExtensionStatusIcon($params) {
	$state	= trim(strtolower($params['state']));
	$states	= array(
		1		=> 'stable',
		2		=> 'beta',
		3		=> 'alpha',
		'alpha'	=> 'alpha',
		'beta'	=> 'beta',
		'stable'=> 'stable'
	);

	if( ! array_key_exists($state, $states) ) {
		$state	= 'alpha';
	} else {
		$state	= $states[$state];
	}

	return '<span class="extensionstate ' . $state . '"></span>';
}



/**
 * Returns a wrapped label tag of a right, evoking right-info tooltip on rollOver
 *
 * @param	array			$params
 * @return	string
 */
function smarty_function_rightLabel($params) {
	$htmlID		= ($params['prefix'] ?? 'right') . '-' . $params['extension'] . '-' . $params['sectionName'] . '-' . $params['right'];
	$attributes	= array(
		'id'	=> $htmlID,
		'class'	=> 'require ' . trim('quickInfoRight ' . ($params['class'] ?? ''))
	);

	$rightTag		= TodoyuString::buildHtmlTag($params['tag'] ?? 'span', $attributes, '');
	$quickInfoScript= TodoyuString::wrapScript('Todoyu.Ext.sysmanager.QuickInfoRight.add(\'' . $htmlID . '\');');

	return $rightTag . $quickInfoScript;
}

