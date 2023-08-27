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
 * Holiday event
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarEventHoliday extends TodoyuBaseObject implements TodoyuCalendarEvent {

	/**
	 * Initialize
	 *
	 * @param	integer		$idHoliday
	 */
	public function __construct($idHoliday) {
		parent::__construct($idHoliday, 'ext_calendar_holiday');
	}



	/**
	 * Get title
	 *
	 * @return	string
	 */
	public function getTitle() {
		return $this->get('title');
	}



	/**
	 * Get description
	 *
	 * @return	string
	 */
	public function getDescription() {
		return $this->get('description');
	}



	/**
	 * Get range
	 *
	 * @return	TodoyuCalendarRangeDay
	 */
	public function getRange() {
		return new TodoyuCalendarRangeDay($this->getDateStart());
	}



	/**
	 * Get start date
	 *
	 * @return	integer
	 */
	public function getDateStart() {
		return $this->getInt('date');
	}



	/**
	 * Get end date
	 *
	 * @return	integer
	 */
	public function getDateEnd() {
		return TodoyuTime::getDayEnd($this->get('date'));
	}



	/**
	 * Get duration
	 *
	 * @return	integer
	 */
	public function getDuration() {
		return TodoyuTime::SECONDS_HOUR * 8 - $this->getWorkingTime();
	}



	/**
	 * A holiday is always a day event
	 *
	 * @return	boolean
	 */
	public function isDayEvent() {
		return true;
	}



	/**
	 * Holidays are never private
	 *
	 * @return	boolean
	 */
	public function isPrivate() {
		return false;
	}



	/**
	 * No persons are assigned to holidays
	 *
	 * @return	Array
	 */
	public function getAssignedPersons() {
		return array();
	}



	/**
	 * No one assigned
	 *
	 * @param	integer		$idPerson
	 * @return	boolean
	 */
	public function isPersonAssigned($idPerson = 0) {
		return false;
	}




	/**
	 * Get working time (in minutes)
	 *
	 * @return	integer
	 */
	public function getWorkingTime() {
		return $this->getInt('workingtime');
	}



	/**
	 * Not overlapping
	 *
	 * @param	TodoyuCalendarEvent		$event
	 * @return	boolean
	 */
	public function isOverlapping(TodoyuCalendarEvent $event) {
		return false;
	}



	/**
	 * No one can edit holidays
	 *
	 * @return	boolean
	 */
	public function canEdit() {
		return false;
	}



	/**
	 * No one has access
	 *
	 * @return	boolean
	 */
	public function hasAccess() {
		return false;
	}



	/**
	 * Get source name
	 *
	 * @return	string
	 */
	public function getSource() {
		return 'holiday';
	}



	/**
	 * Get type name (as in static events)
	 *
	 * @return	string
	 */
	public function getType() {
		return 'holiday';
	}



	/**
	 * Get template data
	 *
	 * @param	boolean		$loadForeignData
	 * @return	Array
	 */
	public function getTemplateData($loadForeignData = false) {
		return parent::getTemplateData();
	}



	/**
	 * Add data to quick info
	 *
	 * @param	TodoyuQuickinfo			$quickInfo
	 * @param	TodoyuDayRange|null		$currentRange
	 */
	public function addQuickInfos(TodoyuQuickinfo $quickInfo, TodoyuDayRange $currentRange = null) {
		$quickInfo->addInfo('title', $this->getTitle());
		$quickInfo->addInfo('type', Todoyu::Label('calendar.ext.holidayset.attr.holiday'));
		$quickInfo->addInfo('date', TodoyuTime::format($this->getDateStart(), 'date'));
		$quickInfo->addInfo('work', round($this->getWorkingTime() / 3600, 1) . ' ' . Todoyu::Label('core.date.time.hours'));
	}



	/**
	 * Get class names
	 *
	 * @return	string[]
	 */
	public function getClassNames() {
		return array();
	}

}

?>