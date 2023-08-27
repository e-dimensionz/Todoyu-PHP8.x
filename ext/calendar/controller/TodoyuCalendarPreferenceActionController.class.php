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
 * Preference action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarPreferenceActionController extends TodoyuActionController {

	/**
	 * Preference value
	 *
	 * @var	String
	 */
	protected $value	= '';

	/**
	 * Preference item
	 *
	 * @var	Integer
	 */
	protected $item		= 0;



	/**
	 * Init
	 *
	 * @param	array	$params
	 */
	public function init(array $params = array()) {
		Todoyu::restrict('calendar', 'general:use');
		Todoyu::restrictInternal();

		$this->value	= $params['value'];
		$this->item		= intval($params['item']);
	}



	/**
	 * Save current active tab
	 *
	 * @param	array		$params
	 */
	public function tabAction(array $params) {
		$tabKey	= trim($params['tab']);

		TodoyuCalendarPreferences::saveActiveTab($tabKey);
	}



	/**
	 * Save current date
	 *
	 * @param	array		$params
	 */
	public function dateAction(array $params) {
		$date	= intval($this->value);

		TodoyuCalendarPreferences::saveDate($date, AREA);
	}



	/**
	 * Saves eventTypeSelector widget preferences (selected event types)
	 *
	 * @param	array	$params
	 */
	public function panelwidgeteventtypeselectorAction(array $params) {
		$eventTypes	= TodoyuArray::intExplode(',', $this->value, true, true);

		TodoyuCalendarPreferences::saveEventTypes($eventTypes);
	}



	/**
	 * Saves HolidaySetSelector widget preferences (selected holidaySets)
	 *
	 * @param	array	$params
	 */
	public function panelwidgetholidaysetselectorAction(array $params) {
		$holidaySets	= TodoyuArray::intExplode(',', $this->value, true, false);

		TodoyuCalendarPreferences::saveHolidaySets($holidaySets);
	}



	/**
	 * Saves viewing mode: full-day / working hours
	 *
	 * @param	array	$params
	 */
	public function fulldayviewAction(array $params) {
		$fullDay	= intval($this->value) === 1;

		TodoyuCalendarPreferences::saveFullDayView($fullDay);
	}



	/**
	 * Toggles weekend display mode: on / off
	 *
	 * @param	array	$params
	 */
	public function toggleDisplayWeekendAction(array $params) {
		$isWeekendDisplayed	= TodoyuCalendarPreferences::isWeekendDisplayed();

		TodoyuCalendarPreferences::saveWeekendDisplayed(! $isWeekendDisplayed);
	}



	/**
	 * Save portal preference: event entry expanded?
	 *
	 * @param	array	$params
	 */
	public function portalEventExpandedAction(array $params) {
		$idEvent	= $this->item;
		$expanded	= intval($this->value) === 1;

		TodoyuCalendarPreferences::savePortalEventExpandedStatus($idEvent, $expanded);
	}



	/**
	 * General panelWidget action, saves collapse status
	 *
	 * @param	array	$params
	 */
	public function pwidgetAction(array $params) {
		$idWidget	= $params['item'];
		$value		= $params['value'];

		TodoyuPanelWidgetManager::saveCollapsedStatus(EXTID_CALENDAR, $idWidget, $value);
	}

}

?>