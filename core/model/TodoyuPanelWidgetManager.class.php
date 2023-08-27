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
 * Panel widget manager
 *
 * @package		Todoyu
 * @subpackage	Core
 */
class TodoyuPanelWidgetManager {

	/**
	 * Configs for panel widgets
	 *
	 * @var	Array
	 */
	private static $panelWidgets = array();


	/**
	 * Add a panel widget for an area
	 *
	 * @param	string		$area		Area where to display the widget
	 * @param	string		$ext		Extension which implements the widget
	 * @param	string		$name		Name of the widget
	 * @param	integer		$position
	 * @param	array		$config
	 */
	public static function addPanelWidget($area, $ext, $name, $position, array $config = array()) {
		$className	= self::buildClassName($ext, $name);
		$idArea		= TodoyuExtensions::getExtID($area);

		self::$panelWidgets[$idArea][$className] = array(
			'class'		=> $className,
			'ext'		=> $ext,
			'name'		=> $name,
			'position'	=> (int) $position,
			'config'	=> $config,
			'area'		=> $area
		);
	}



	/**
	 * Get panel widget config
	 *
	 * @param	string		$ext
	 * @param	string		$name
	 * @return	Array
	 */
	public static function getConfig($ext, $name) {
		TodoyuExtensions::loadAllPanelWidget();

		$className	= self::buildClassName($ext, $name);

		return TodoyuArray::assure(self::$panelWidgets[AREA][$className] ?? []);
	}



	/**
	 * Check whether a panel widgets exists in the current area
	 *
	 * @param	string		$area
	 * @param	string		$ext
	 * @param	string		$name
	 * @return	boolean
	 */
	public static function exists($area, $ext, $name) {
		TodoyuExtensions::loadAllPanelWidget();

		$className	= self::buildClassName($ext, $name);

		return isset(self::$panelWidgets[$area][$className]);
	}



	/**
	 * Get config parameter from widget config
	 *
	 * @param	string		$ext
	 * @param	string		$name
	 * @return	Array
	 */
	public static function getCustomConfig($ext, $name) {
		$config	= self::getConfig($ext, $name);

		return TodoyuArray::assure($config['config'] ?? []);
	}



	/**
	 * Get panel widget configs for area
	 *
	 * @param	string		$area
	 * @return	Array
	 */
	public static function getAreaPanelWidgets($area) {
		TodoyuExtensions::loadAllPanelWidget();
		$idArea	= TodoyuExtensions::getExtID($area);

		if( is_array(self::$panelWidgets[$idArea]) ) {
			return TodoyuArray::sortByLabel(self::$panelWidgets[$idArea], 'position');
		}

		return array();
	}



	/**
	 * Create a new panel widget with given params
	 *
	 * @param	string		$widgetClassName
	 * @param	integer		$area
	 * @param	array		$params
	 * @return	TodoyuPanelWidget
	 */

	/**
	 * Get panel widget instance
	 *
	 * @param	string	$ext
	 * @param	string	$name
	 * @param	array	$params
	 * @return	TodoyuPanelWidget
	 */
	public static function getPanelWidget($ext, $name, array $params = array()) {
		TodoyuExtensions::loadAllPanelWidget();

		$className		= self::buildClassName($ext, $name);
		$customConfig	= self::getCustomConfig($ext, $name);

		if( ! array_key_exists($className, self::$panelWidgets) ) {
			self::$panelWidgets[$className] = new $className($customConfig, $params);
		}

		return self::$panelWidgets[$className];
	}



	/**
	 * Get class name for panel widget
	 *
	 * @param	string		$ext
	 * @param	string		$name
	 * @return	string
	 */
	public static function buildClassName($ext, $name) {
		return 'Todoyu' . ucfirst(strtolower($ext)) . 'PanelWidget' . ucfirst($name);
	}



	/**
	 * Save collapsed status
	 *
	 * @param	string		$widget
	 * @param	boolean		$expanded
	 */
	public static function saveCollapsedStatus($widget, $expanded = true) {
		$preference	= 'pwidget-collapsed-' . strtolower($widget);

		if( $expanded ) {
			TodoyuPreferenceManager::deletePreference(0, $preference, null, 0, AREA);
		} else {
			TodoyuPreferenceManager::savePreference(0, $preference, 1, 0, false, AREA);
		}
	}



	/**
	 * Check if a panelwidget is collapsed
	 *
	 * @param	string		$widget
	 * @return	boolean
	 */
	public static function isCollapsed($widget) {
		$pref	= TodoyuPreferenceManager::getPreference(0, 'pwidget-collapsed-' . $widget, 0, AREA);

		return ((int) $pref) === 1;
	}
}

?>