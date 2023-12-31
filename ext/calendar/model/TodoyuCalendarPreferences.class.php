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
 * Calendar Preferences
 *
 * @package		Todoyu
 * @subpackage	Calendar
*/
class TodoyuCalendarPreferences {

	/**
	 * Save calendar extension preference
	 *
	 * @param	integer		$preference
	 * @param	string		$value
	 * @param	integer		$idItem
	 * @param	boolean		$unique
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 */
	public static function savePref($preference, $value, $idItem = 0, $unique = false, $idArea = 0, $idPerson = 0) {
		TodoyuPreferenceManager::savePreference(EXTID_CALENDAR, $preference, $value, $idItem, $unique, $idArea, $idPerson);
	}



	/**
	 * Check if preference is set
	 * Without $value, it checks if a preference is stored, else it checks if
	 * a preference with exactly this value is stored
	 *
	 * @param	string		$preference		Preference name
	 * @param	integer		$idItem			ID of the item
	 * @param	string		$value			Stored value
	 * @param	integer		$idArea			ID of the area
	 * @param	integer		$idPerson		User ID
	 * @return	boolean
	 */
	public static function isPreferenceSet($preference, $idItem = 0, $value = null, $idArea = 0, $idPerson = 0) {
		return TodoyuPreferenceManager::isPreferenceSet(EXTID_CALENDAR, $preference, $idItem, $value, $idArea, $idPerson);
	}



	/**
	 * Get given calendar extension preference
	 *
	 * @param	string		$preference
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	boolean		$unserialize
	 * @param	integer		$idPerson
	 * @return	string|Boolean
	 */
	public static function getPref($preference, $idItem = 0, $idArea = 0, $unserialize = false, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreference(EXTID_CALENDAR, $preference, $idItem, $idArea, $unserialize, $idPerson);
	}



	/**
	 * Get calendar extension preferences
	 *
	 * @param	string		$preference
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 * @return	Array
	 */
	public static function getPrefs($preference, $idItem = 0, $idArea = 0, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreferences(EXTID_CALENDAR, $preference, $idItem, $idArea, $idPerson);
	}



	/**
	 * Get date for calendar
	 *
	 * @param	integer	$idArea
	 * @return	integer
	 */
	public static function getDate($idArea = 0) {
		$date	= self::getPref('date', 0, $idArea);

		return TodoyuTime::time($date);
	}



	/**
	 * Save date for calendar
	 *
	 * @param	integer		$date
	 * @param	integer		$idArea
	 */
	public static function saveDate($date, $idArea = 0) {
		$date	= intval($date);

		self::savePref('date', $date, 0, true, $idArea);
	}



	/**
	 * Save full-day view preference (active?)
	 *
	 * @param	boolean		$full
	 */
	public static function saveFullDayView($full = true) {
		$full	= $full ? 1 : 0;

		self::savePref('fulldayview', $full, 0, true);
	}



	/**
	 * Save display weekend preference (on/off)
	 *
	 * @param	boolean	$displayed
	 */
	public static function saveWeekendDisplayed($displayed = true) {
		$displayed	= $displayed ? 1 : 0;

		self::savePref('displayweekend', $displayed, 0, true);
	}



	/**
	 * Get full-day view (active?) preference
	 *
	 * @return	boolean
	 */
	public static function isWeekendDisplayed() {
		$pref	= self::getPref('displayweekend');

		return intval($pref) === 1;
	}



	/**
	 * Get full-day view (active?) preference
	 *
	 * @return	boolean
	 */
	public static function getFullDayView() {
		$pref	= self::getPref('fulldayview', 0);

		return intval($pref) === 1;
	}



	/**
	 * Get beginning hour of time excerpt preference
	 *
	 * @return	integer
	 */
	public static function getCompactViewRangeStart() {
		if( self::isPreferenceSet('range_start') ) {
			$rangeStart	= self::getPref('range_start');
		} else {
			$rangeStart = CALENDAR_RANGE_START;
		}

		return $rangeStart;
	}



	/**
	 * Get ending hour of time excerpt preference
	 *
	 * @return	integer
	 */
	public static function getCompactViewRangeEnd() {
		if( self::isPreferenceSet('range_end') ) {
			$rangeStart	= self::getPref('range_end');
		} else {
			$rangeStart = CALENDAR_RANGE_END;
		}

		return $rangeStart;
	}



	/**
	 * Save selected event types
	 *
	 * @param	array		$types
	 */
	public static function saveEventTypes(array $types) {
		$types	= implode(',', $types);

		self::savePref('panelwidget-eventtypeselector', $types, 0, true, AREA);
	}



	/**
	 * Save selected holiday sets
	 *
	 * @param	array	$setIDs
	 */
	public static function saveHolidaySets($setIDs) {
		$setIDs	= TodoyuArray::intval($setIDs);

			// 'no set'-option selected? deselect all other options
		if( in_array(0, $setIDs) ) {
			$setIDs	= array(0);
		}

		self::savePref('panelwidget-holidaysetselector', implode(',', $setIDs), 0, true, AREA);
	}



	/**
	 * Gets the current active tab
	 *
	 * @return	string	tab name
	 */
	public static function getActiveTab() {
		$tab = self::getPref('tab');

		return $tab ? $tab : Todoyu::$CONFIG['EXT']['calendar']['config']['defaultTab'];
	}



	/**
	 * Save the current active tab as pref
	 *
	 * @param	string		$tabKey		Name of the tab
	 */
	public static function saveActiveTab($tabKey) {
		self::savePref('tab', $tabKey, 0, true);
	}



	/**
	 * Get the saved calendar date pref. If not set, return timestamp of now
	 *
	 * @return	integer		Timestamp
	 */
	public static function getCalendarDate() {
		$time	= self::getPref('date');

		return TodoyuTime::time($time);
	}



	/**
	 * Save the active calendar date.
	 *
	 * @param	integer		$idArea
	 * @param	integer		$timestamp		UNIX Timestamp
	 */
	public static function saveCalendarDate($idArea, $timestamp) {
		$timestamp	= intval($timestamp);

		TodoyuPreferenceManager::savePreference(EXTID_CALENDAR, 'date', $timestamp, 0, true, $idArea);
	}



	/**
	 * Save event display preference: expanded?
	 *
	 * @param	integer		$idEvent
	 * @param	boolean		$expanded
	 */
	public static function savePortalEventExpandedStatus($idEvent, $expanded = true) {
		$idEvent= intval($idEvent);
		$value	= $expanded ? 1 : 0;

		self::savePref('portal-event-expanded', $value, $idEvent, true);
	}



	/**
	 * Get event display preference: expanded?
	 *
	 * @param	integer		$idEvent
	 * @return	boolean
	 */
	public static function getPortalEventExpandedStatus($idEvent) {
		$idEvent= intval($idEvent);

		return intval(self::getPref('portal-event-expanded', $idEvent)) === 1;
	}



	/**
	 * Save reminder email advance time
	 *
	 * @param	integer		$timestamp
	 */
	public static function saveReminderEmailTime($timestamp) {
		self::saveReminderTime('email', $timestamp);
	}



	/**
	 * Save reminder popup advance time
	 *
	 * @param	integer		$timestamp
	 */
	public static function saveReminderPopupTime($timestamp) {
		self::saveReminderTime('popup', $timestamp);
	}



	/**
	 * Save reminder email advance time
	 *
	 * @param	integer		$idPerson
	 * @return	integer
	 */
	public static function getReminderEmailTime($idPerson = 0) {
		return self::getReminderAdvanceTime('email', $idPerson);
	}



	/**
	 * Save reminder popup advance time
	 *
	 * @param	integer		$idPerson
	 * @return	integer
	 */
	public static function getReminderPopupTime($idPerson = 0) {
		return self::getReminderAdvanceTime('popup', $idPerson);
	}



	/**
	 * Save reminder advance time
	 *
	 * @param	string		$typeKey
	 * @param	integer		$timestamp
	 */
	private static function saveReminderTime($typeKey, $timestamp) {
		$prefName	= 'reminder' . $typeKey . '_advancetime';
		$timestamp		= intval($timestamp);

		self::savePref($prefName, $timestamp, 0, true);
	}



	/**
	 * Get reminder advance time
	 *
	 * @param	string				$typeKey
	 * @param	integer				$idPerson
	 * @return	integer|Boolean
	 */
	public static function getReminderAdvanceTime($typeKey, $idPerson = 0) {
		$idPerson	= Todoyu::personid($idPerson);
		$prefName	= 'reminder' . $typeKey . '_advancetime';

		return self::getPref($prefName, 0, 0, false, $idPerson);
	}



	/**
	 * Check whether the mail popup is disabled
	 *
	 * @return	boolean
	 */
	public static function isMailPopupDisabled() {
		$prefName	= 'is_mailpopupdeactivated';
		$result		= intval(self::getPref($prefName));

		return $result === 1;
	}


}

?>