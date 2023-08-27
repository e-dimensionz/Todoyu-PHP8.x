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
 * Event interface
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
interface TodoyuCalendarEvent {

	/**
	 * Get ID
	 *
	 * @return	integer
	 */
	public function getID();

	/**
	 * Get title
	 *
	 * @return	string
	 */
	public function getTitle();

	/**
	 * Get description
	 *
	 * @return	string
	 */
	public function getDescription();

	/**
	 * Get start date
	 *
	 * @return	integer
	 */
	public function getDateStart();

	/**
	 * Get end date
	 *
	 * @return	integer
	 */
	public function getDateEnd();

	/**
	 * Get duration in seconds
	 *
	 * @return	integer
	 */
	public function getDuration();

	/**
	 * Get range
	 *
	 * @return	TodoyuDateRange
	 */
	public function getRange();

	/**
	 * Get assigned persons
	 *
	 * @return	TodoyuContactPerson[]
	 */
	public function getAssignedPersons();

	/**
	 * Get source name
	 *
	 * @return	string
	 */
	public function getSource();

	/**
	 * Get type name
	 *
	 * @return	string
	 */
	public function getType();

	/**
	 * Check whether person is assigned
	 *
	 * @param	integer
	 * @return	boolean
	 */
	public function isPersonAssigned($idPerson = 0);

	/**
	 * Check whether event is flagged as day event
	 *
	 * @return	boolean
	 */
	public function isDayEvent();

	/**
	 * check whether event is private
	 *
	 * @return	boolean
	 */
	public function isPrivate();

	/**
	 * Check whether current user has access to the event
	 *
	 * @return	boolean
	 */
	public function hasAccess();

	/**
	 * Check whether the current suer can edit the event
	 *
	 * @return	boolean
	 */
	public function canEdit();

	/**
	 * Get class names for event element
	 *
	 * @return	string[]
	 */
	public function getClassNames();

	/**
	 * Check whether the event is overlapping another event
	 *
	 * @param	TodoyuCalendarEvent		$event
	 * @return	boolean
	 */
	public function isOverlapping(TodoyuCalendarEvent $event);

	/**
	 * Get template data
	 *
	 * @param	boolean		$loadForeignData
	 * @return	Array
	 */
	public function getTemplateData($loadForeignData = false);

	/**
	 * Add quick infos
	 *
	 * @param	TodoyuQuickInfo		$quickInfo
	 * @param	TodoyuDayRange		$range
	 */
	public function addQuickInfos(TodoyuQuickInfo $quickInfo, TodoyuDayRange $range = null);

}

?>