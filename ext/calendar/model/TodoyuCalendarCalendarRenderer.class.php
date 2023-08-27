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
 * Calendar Renderer
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarCalendarRenderer {

	/**
	 * Extension key
	 *
	 * @var	String
	 */
	const EXTKEY	= 'calendar';

	/**
	 * Render content body: calendar of day/week/month, view/edit event
	 *
	 * @param	string		$tab
	 * @param	integer		$date
	 * @param	array		$params
	 * @return	string
	 */
	public static function renderBody($tab, $date = 0, array $params = array()) {
		$date		= TodoyuTime::time($date);
		$idEvent	= intval($params['event'] ?? 0);

		if( $idEvent === 0 && in_array($tab, array('day', 'week', 'month')) ) {
			$eventFilters	= TodoyuCalendarManager::getAllEventFilters();
		} else {
			$eventFilters	= array();
		}

		switch( $tab ) {
				// Calendar views
			case 'day':
				return self::renderBodyDay($date, $eventFilters);
				break;
			case 'week':
				return self::renderBodyWeek($date, $eventFilters);
				break;
			case 'month':
				return self::renderBodyMonth($date, $eventFilters);
				break;

				// Event view/edit
			case 'view':
				return self::renderBodyView($idEvent);
				break;
			case 'edit':
				return self::renderBodyEdit($idEvent);
				break;

			default:
				return 'Invalid type';
		}
	}



	/**
	 * Render calendar view for day view
	 *
	 * @param	integer		$date
	 * @param	array		$filters
	 * @return	string
	 */
	public static function renderBodyDay($date, array $filters) {
		$date	= intval($date);
		$view	= new TodoyuCalendarViewDay($date, $filters);

		return $view->render();
	}



	/**
	 * Render calendar view for week view
	 *
	 * @param	integer		$date
	 * @param	array		$filters
	 * @return	string
	 */
	public static function renderBodyWeek($date, array $filters) {
		$date	= intval($date);
		$view	= new TodoyuCalendarViewWeek($date, $filters);

		return $view->render();
	}



	/**
	 * Render calendar view for month view
	 *
	 * @param	integer		$date
	 * @param	array		$filters
	 * @return	string
	 */
	public static function renderBodyMonth($date, array $filters) {
		$date	= intval($date);
		$view	= new TodoyuCalendarViewMonth($date, $filters);

		return $view->render();
	}



	/**
	 * Render calendar body for event detail view
	 *
	 * @param	integer		$idEvent
	 * @return	string
	 */
	public static function renderBodyView($idEvent) {
		$idEvent	= intval($idEvent);

		return TodoyuCalendarEventRenderer::renderEventView($idEvent);
	}



	/**
	 * Render calendar body for event edit
	 *
	 * @param	integer		$idEvent
	 * @return	string
	 */
	public static function renderBodyEdit($idEvent) {
		$idEvent	= intval($idEvent);

		return TodoyuCalendarEventEditRenderer::renderEventForm($idEvent);
	}

}

?>