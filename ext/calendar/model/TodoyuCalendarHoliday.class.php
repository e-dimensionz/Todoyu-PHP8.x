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
 * Holiday object
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarHoliday extends TodoyuBaseObject {

	/**
	 * @var	String		Default table for database requests
	 */
	const TABLE	= 'ext_calendar_holiday';



	/**
	 * Constructor
	 *
	 * @param	integer		$idHoliday
	 */
	public function __construct($idHoliday) {
		$idHoliday	= intval($idHoliday);

		parent::__construct($idHoliday, self::TABLE);
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
	 * Get holiday label
	 *
	 * @return	string
	 */
	public function getLabel() {
		return $this->getTitle();
	}



	/**
	 * Get date
	 *
	 * @return	integer
	 */
	public function getDate() {
		return $this->getInt('date');
	}



	/**
	 * Get description
	 *
	 * @return	string
	 */
	public function getDescripion() {
		return $this->get('description');
	}



	/**
	 * Get working time
	 *
	 * @return	integer
	 */
	public function getWorkingTime() {
		return $this->getInt('workingtime');
	}



	/**
	 * Load foreign data
	 */
	public function loadForeignData() {
		$this->data['holidayset']	= TodoyuCalendarHolidayManager::getHolidaySets($this->getID());
	}



	/**
	 * Get template data for holiday
	 *
	 * @param	boolean		$loadForeignData
	 * @return	Array
	 */
	public function getTemplateData($loadForeignData	= false) {
		if( $loadForeignData ) {
			$this->loadForeignData();
		}

		return parent::getTemplateData();
	}

}

?>