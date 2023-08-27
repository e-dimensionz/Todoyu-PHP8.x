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
 * Series of events
 * Records holds the pattern on which the static events were generated
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarEventSeries extends TodoyuBaseObject {

	/**
	 * Data of the base event
	 *
	 * @var	Array
	 */
	protected $eventData = array();


	/**
	 * Initialize
	 *
	 * @param	integer		$idSeries
	 */
	public function __construct($idSeries) {
		parent::__construct($idSeries, 'ext_calendar_series');
	}



	/**
	 * Get frequency (index constant)
	 *
	 * @return	integer
	 */
	public function getFrequency() {
		return $this->getInt('frequency');
	}



	/**
	 * Check whether series has no frequency configured = not active
	 *
	 * @return	boolean
	 */
	public function hasNoFrequency() {
		return !$this->hasFrequency();
	}



	/**
	 * Check whether series is configured
	 *
	 * @return	boolean
	 */
	public function hasFrequency() {
		return $this->getFrequency() !== 0;
	}



	/**
	 * Check whether series mode day is set
	 *
	 * @return	boolean
	 */
	public function hasFrequencyDay() {
		return $this->getFrequency() === CALENDAR_SERIES_FREQUENCY_DAY;
	}



	/**
	 * Check whether series has week frequency
	 *
	 * @return	boolean
	 */
	public function hasFrequencyWeek() {
		return $this->getFrequency() === CALENDAR_SERIES_FREQUENCY_WEEK;
	}



	/**
	 * Check whether series has weekday frequency
	 *
	 * @return	boolean
	 */
	public function hasFrequencyWeekday() {
		return $this->getFrequency() === CALENDAR_SERIES_FREQUENCY_WEEKDAY;
	}



	/**
	 * Check whether series mode month is set
	 *
	 * @return	boolean
	 */
	public function hasFrequencyMonth() {
		return $this->getFrequency() === CALENDAR_SERIES_FREQUENCY_MONTH;
	}



	/**
	 * Check whether series mode year is set
	 *
	 * @return	boolean
	 */
	public function hasFrequencyYear() {
		return $this->getFrequency() === CALENDAR_SERIES_FREQUENCY_YEAR;
	}



	/**
	 * Get interval
	 *
	 * @return	integer
	 */
	public function getInterval() {
		$interval	= $this->getInt('interval');

		return $interval === 0 ? 1 : $interval;
	}



	/**
	 * Check whether interval is 1
	 *
	 * @return	boolean
	 */
	public function isOneStepInterval() {
		return $this->getInterval() === 1;
	}



	/**
	 * Get start date
	 *
	 * @return	integer
	 */
	public function getDateStart() {
		return $this->getInt('date_start');
	}



	/**
	 * Get end date
	 *
	 * @return	integer
	 */
	public function getDateEnd() {
		return $this->getInt('date_end');
	}



	/**
	 * Get date end for range calculation
	 *
	 * @return	integer
	 */
	protected function getDateEndForRanges() {
		$dateEnd	= $this->getDateEnd();

		if( $dateEnd !== 0 ) {
			$dateEnd = TodoyuTime::getDayEnd($dateEnd);
		}

		return $dateEnd;
	}



	/**
	 * Check whether series has end date
	 *
	 * @return	boolean
	 */
	public function hasEndDate() {
		return $this->getDateEnd() !== 0;
	}




	/**
	 * Get configured day in month for monthly frequency
	 *
	 * @return	integer
	 */
	public function getMonthDay() {
		return $this->getInt('config');
	}



	/**
	 * Get month day of event start date
	 *
	 * @return	integer
	 */
	public function getMonthDayFromDate() {
		$date	= $this->getEventDate();

		return intval(date('j', $date));
	}



	/**
	 * Get year config parts
	 * [month => 1, day => 15]
	 *
	 * @return	Array
	 */
	public function getYearParts() {
		$yearParts	= $this->getConfig();

		if( empty($yearParts) ) {
			if( $this->getEventDate() !== 0 ) {
				$yearParts = array(
					'month'	=> date('n', $this->getEventDate()),
					'day'	=> date('j', $this->getEventDate())
				);
			}
		}

		return $yearParts;
	}



	/**
	 * Get configured week days
	 *
	 * @return	string[]
	 */
	public function getWeekDays() {
		$config	= $this->getConfig();

			// Get defaults if no config is set
		if( empty($config)) {
			$config = $this->getDefaultWeekDays();
		}

		return $config;
	}



	/**
	 * Get indexes of selected weekdays
	 * Only available on frequency week. According to date() function
	 *
	 * @return	integer[]
	 */
	protected function getWeekDayIndexes() {
		$weekDays	= $this->getWeekDays();
		$map		= Todoyu::$CONFIG['EXT']['calendar']['weekDays']['index'];
		$indexes	= array();

		foreach($weekDays as $weekDay) {
			$indexes[] = $map[$weekDay];
		}

		return $indexes;
	}



	/**
	 * Get default selected week day key
	 *
	 * @return	Array
	 */
	protected function getDefaultWeekDays() {
		$eventDate = $this->getEventDate();

		if( $eventDate === 0 ) {
			$weekDays[] = 'mo';
		} else {
			$weekDays[] = TodoyuCalendarManager::getWeekDayKey($eventDate);
		}

		return $weekDays;
	}



	/**
	 * Get config array
	 *
	 * @return	Array
	 */
	protected function getConfig() {
		$config	= trim($this->get('config'));

		if( $config === '' ) {
			return array();
		} else {
			return TodoyuArray::assure(json_decode($config, true));
		}
	}



	/**
	 * Get configured week days with labels
	 *
	 * @return	string[]
	 */
	public function getWeekDayLabels() {
		$weekDays	= $this->getWeekDays();

		foreach($weekDays as $index => $dayKey) {
			$weekDays[$index] = TodoyuCalendarManager::getWeekDayLabel($dayKey);
		}

		return $weekDays;
	}



	/**
	 * Get start date of event
	 *
	 * @return	integer
	 */
	public function getEventDate() {
		$dateStart	= $this->eventData['date_start'];

		if( is_numeric($dateStart) ) {
			$eventDate	= intval($dateStart);
		} else {
			$eventDate	= TodoyuTime::parseDateTime($dateStart);
		}
		
		return $eventDate;
	}




	/**
	 * Get series label
	 *
	 * @return	string
	 */
	public function getLabel() {
		switch( $this->getFrequency() ) {
			case CALENDAR_SERIES_FREQUENCY_DAY:
				$label = $this->getLabelDay();
				break;
			case CALENDAR_SERIES_FREQUENCY_WEEKDAY:
				$label = $this->getLabelWeekday();
				break;
			case CALENDAR_SERIES_FREQUENCY_WEEK:
				$label = $this->getLabelWeek();
				break;
			case CALENDAR_SERIES_FREQUENCY_MONTH:
				$label = $this->getLabelMonth();
				break;
			case CALENDAR_SERIES_FREQUENCY_YEAR:
				$label = $this->getLabelYear();
				break;
			default:
				$label	= 'No Series selected';
		}

		return $label;
	}



	/**
	 * Get label for day
	 *
	 * @return	string
	 */
	protected function getLabelDay() {
		if( $this->isOneStepInterval() ) {
			$label = Todoyu::Label('calendar.series.label.day');
		} else {
			$label = TodoyuLabelManager::getFormatLabel('calendar.series.label.days', array($this->getInterval()));
		}

		return $this->appendDateEndLabel($label);
	}



	/**
	 * Get label for weekday
	 *
	 * @return	string
	 */
	protected function getLabelWeekday() {
		$label	= Todoyu::Label('calendar.series.label.weekday');

		return $this->appendDateEndLabel($label);
	}



	/**
	 * Get label for week
	 *
	 * @return	string
	 */
	protected function getLabelWeek() {
		$weekDays	= $this->getWeekDayLabels();

		if( $this->isOneStepInterval() ) {
			$label	= Todoyu::Label('calendar.series.label.week');
		} else {
			$label	= TodoyuLabelManager::getFormatLabel('calendar.series.label.weeks', array($this->getInterval()));
		}

		if( sizeof($weekDays) === 7 ) {
			$label .= ' ' . Todoyu::Label('calendar.series.label.week.allDays');
		} else {
			$label .= ' ' . TodoyuLabelManager::getFormatLabel('calendar.series.label.week.days', array(implode(', ', $weekDays)));
		}

		return $this->appendDateEndLabel($label);
	}



	/**
	 * Get label for month
	 *
	 * @return	string
	 */
	protected function getLabelMonth() {
		if( $this->isOneStepInterval() ) {
			$label = Todoyu::Label('calendar.series.label.month');
		} else {
			$label = TodoyuLabelManager::getFormatLabel('calendar.series.label.months', array($this->getInterval()));
		}

		$monthDay	= $this->getMonthDay();

		if( $monthDay === 0 ) {
			$monthDay = $this->getMonthDayFromDate();
		}

		$label .= ' ' . TodoyuLabelManager::getFormatLabel('calendar.series.label.month.atDay', array($monthDay));

		return $this->appendDateEndLabel($label);
	}



	/**
	 * Get label for year
	 *
	 * @return	string
	 */
	protected function getLabelYear() {
		$yearParts	= $this->getYearParts();

		if( $this->isOneStepInterval() ) {
			$label	= Todoyu::Label('calendar.series.label.year');
		} else {
			$label	= TodoyuLabelManager::getFormatLabel('calendar.series.label.years', array($this->getInterval()));
		}

		$dummyDate	= mktime(0, 0, 0, $yearParts['month'], $yearParts['day']);
		$date		= TodoyuTime::format($dummyDate, 'MlongD2');

		$label .= ' ' . Todoyu::Label('calendar.series.label.year.atDate') . ' ' . $date;

		return $this->appendDateEndLabel($label);
	}



	/**
	 * Append date end label to series label
	 *
	 * @param	string	$label		Label from frequency label function
	 * @return	string	Label with date end appended if set
	 */
	protected function appendDateEndLabel($label) {
		if( $this->getDateEnd() ) {
			$date	= TodoyuTime::format($this->getDateEnd(), 'DlongD2MlongY4');
			$label .= ', ' . TodoyuLabelManager::getFormatLabel('calendar.series.label.until', array($date));
		}

		return $label;
	}




	/**
	 * Get template data
	 *
	 * @return	Array
	 */
	public function getTemplateData() {
		$data	= parent::getTemplateData();

		$data['label']	= $this->getLabel();

		return $data;
	}



	/**
	 * Set data from form (convert to interval storage names)
	 * Date is a reference date for first calculation
	 *
	 * @param	array		$formData
	 */
	public function setFormData(array $formData) {
		$frequency = $this->getValueFromFormData($formData, 'seriesfrequency');

		if( $frequency ) {
			$data	= array(
				'frequency'	=> $frequency,
				'interval'	=> $this->getValueFromFormData($formData, 'seriesinterval'),
				'date_end'	=> $this->parseDate($formData['seriesdate_end'])
			);

			if( $frequency === CALENDAR_SERIES_FREQUENCY_WEEK ) {
				if( isset($formData['seriesweekdays']) ) {
					if( !is_array($formData['seriesweekdays']) ) {
						$formData['seriesweekdays'] = $formData['seriesweekdays'] == '' ? array() : explode(',', $formData['seriesweekdays']);
					}
					$data['config']	= json_encode(TodoyuArray::assure($formData['seriesweekdays']));
				}
			}

			$this->data	= array_merge($this->data, $data);
		}

		if( isset($formData['date_start']) ) {
			$this->eventData	= $formData;
		}
	}



	/**
	 * Parse a date
	 * Possible formats: timestamp, date, datetime
	 *
	 * @param	string|Integer	$date
	 * @param	boolean		$withTime
	 * @return	integer
	 */
	private function parseDate($date, $withTime = false) {
		if( is_numeric($date) ) {
			return intval($date);
		} else {
			return $withTime ? TodoyuTime::parseDateTime($date) : TodoyuTime::parseDate($date);
		}
	}



	/**
	 * Get data for event form (with series prefixes)
	 *
	 * @return	Array
	 */
	public function getFormData() {
		$formData	= array(
			'id_series'			=> $this->getID(),
			'seriesfrequency'	=> $this->getFrequency(),
			'seriesinterval'	=> $this->getInterval(),
			'seriesdate_end'	=> $this->getDateEnd()
		);

		if( $this->getFrequency() === CALENDAR_SERIES_FREQUENCY_WEEK ) {
			$formData['seriesweekdays'] = $this->getWeekDays();
		}

		return $formData;
	}


	/**
	 * Get series sub form
	 *
	 * @return	TodoyuForm
	 */
	public function getForm() {
		$xmlPath= 'ext/calendar/config/form/event-series.xml';
		$form	= TodoyuFormManager::getForm($xmlPath, $this->getID());
		$form->setFormData($this->getFormData());
		$form->setVars(array(
			'eventData' => TodoyuArray::assure($this->eventData)
		));
		
		return $form;
	}



	/**
	 * Render only the series fields for live refresh
	 *
	 * @return	string
	 */
	public function renderSeriesFields() {
		return $this->getFieldset(false)->renderElements();
	}



	/**
	 * Get ranges for series based on the base event
	 *
	 * @param	integer		$dateStart
	 * @param	integer		$limit
	 * @return	integer[]
	 */
	public function getNextStartDates($dateStart, $limit = 0) {
		$limit		= intval($limit);
		$dateStart	= $this->getFixedStartDate($dateStart);

		if( $limit === 0 ) {
			$limit		= TodoyuCalendarEventSeriesManager::getCreateLimit();
		}

		$freq = $this->getFrequency();
		switch( $freq ) {
			case CALENDAR_SERIES_FREQUENCY_DAY:
				$dates	= $this->getStartDatesDay($dateStart, $limit);
				break;

			case CALENDAR_SERIES_FREQUENCY_WEEKDAY:
				$dates	= $this->getStartDatesWeekday($dateStart, $limit);
				break;

			case CALENDAR_SERIES_FREQUENCY_WEEK:
				$dates	= $this->getStartDatesWeek($dateStart, $limit);
				break;

			case CALENDAR_SERIES_FREQUENCY_MONTH:
				$dates	= $this->getStartDatesMonth($dateStart, $limit);
				break;

			case CALENDAR_SERIES_FREQUENCY_YEAR:
				$dates	= $this->getStartDatesYear($dateStart, $limit);
				break;

			default:
				$dates = array();
		}

		return $dates;
	}



	/**
	 * Get ranges based on next start dates
	 *
	 * @param	integer		$dateStart
	 * @param	integer		$duration
	 * @param	integer		$limit
	 * @return	TodoyuDateRange[]
	 */
	public function getNextRanges($dateStart, $duration, $limit = 0) {
		$startDates	= $this->getNextStartDates($dateStart, $limit);
		$ranges		= array();

		foreach($startDates as $dateStart) {
			$dateEnd	= $dateStart + $duration;
			$ranges[]	= new TodoyuDateRange($dateStart, $dateEnd);
		}

		return $ranges;
	}



	/**
	 * Get event ranges for daily series
	 *
	 * @param	integer			$dateStart
	 * @param	integer			$limit
	 * @return	integer[]
	 */
	private function getStartDatesDay($dateStart, $limit) {
		$counter	= 1;
		$dateEnd	= $this->getDateEndForRanges();
		$date		= $dateStart;
		$interval	= $this->getInterval();
		$dates		= array();

		while( ($dateEnd === 0 && $counter <= $limit) || ($date < $dateEnd) ) {
			$date	= TodoyuTime::addDays($date, $interval);

			if( $dateEnd > 0 && $date > $dateEnd ) {
				break;
			}

			$dates[]= $date;
			$counter++;
		}

		return $dates;
	}



	/**
	 * Get event ranges for weekday series
	 *
	 * @param	integer			$dateStart
	 * @param	integer			$limit
	 * @return	integer[]
	 */
	private function getStartDatesWeekday($dateStart, $limit) {
		$counter	= 1;
		$dateEnd	= $this->getDateEnd();
		$date		= $dateStart;
		$dates		= array();
		$weekendDays= TodoyuTime::isMondayFirstDayOfWeek() ? array(6,0) : array(5,6);

		while( ($dateEnd === 0 && $counter <= $limit) || ($date < $dateEnd) ) {
			$date	= TodoyuTime::addDays($date, 1);
			$weekDay= date('w', $date);

				// Ignore weekends
			if( !in_array($weekDay, $weekendDays) ) {
				$dates[]= $date;
				$counter++;
			}
		}

		return $dates;
	}



	/**
	 * Get event ranges for weekly series
	 *
	 * @param	integer			$dateStart
	 * @param	integer			$limit
	 * @return	integer[]
	 */
	private function getStartDatesWeek($dateStart, $limit) {
		$counter		= 1;
		$dateEnd		= $this->getDateEnd();
		$interval		= $this->getInterval();
		$dates			= array();
		$weekDayIndexes	= $this->getWeekDayIndexes();
		$date			= $this->getStartDateForWeekDay($dateStart);

		while( ($dateEnd === 0 && $counter <= $limit) || ($date < $dateEnd) ) {
			$date	= TodoyuTime::addDays($date, $interval);

			if( $dateEnd > 0 && $date > $dateEnd ) {
				break;
			}

			$weekDay= date('w', $date);

			if( in_array($weekDay, $weekDayIndexes) ) {
				$dates[] = $date;
				$counter++;
			}
		}

		return $dates;
	}



	/**
	 * Get event ranges for monthly series
	 *
	 * @param	integer			$dateStart
	 * @param	integer			$limit
	 * @return	integer[]
	 */
	private function getStartDatesMonth($dateStart, $limit) {
		$year		= date('Y', $dateStart);
		$month		= date('n', $dateStart);
		$day		= date('j', $dateStart);
		$hour		= date('H', $dateStart);
		$minutes	= date('i', $dateStart);
		$counter	= 1;
		$dateEnd	= $this->getDateEnd();
		$date		= $dateStart;
		$interval	= $this->getInterval();
		$dates		= array();

		while( ($dateEnd === 0 && $counter <= $limit) || ($date < $dateEnd) ) {
			$date	= mktime($hour, $minutes, 0, $month+$counter*$interval, $day, $year);

			if( $dateEnd > 0 && $date > $dateEnd ) {
				break;
			}

			$dates[]= $date;
			$counter++;
		}

		return $dates;
	}



	/**
	 * Get event ranges for yearly series
	 *
	 * @param	integer			$dateStart
	 * @param	integer			$limit
	 * @return	integer[]
	 */
	private function getStartDatesYear($dateStart, $limit) {
		$year		= date('Y', $dateStart);
		$month		= date('n', $dateStart);
		$day		= date('j', $dateStart);
		$hour		= date('H', $dateStart);
		$minutes	= date('i', $dateStart);
		$counter	= 1;
		$dateEnd	= $this->getDateEnd();
		$date		= $dateStart;
		$interval	= $this->getInterval();
		$dates		= array();

		while( $counter <= $limit && ($dateEnd === 0 || $date < $dateEnd) ) {
			$nextYear		= $year+$counter*$interval;

				// Later dates are not supported by php (int 32bit)
			if( $nextYear >= 2038 ) {
				break;
			}

			$date	= mktime($hour, $minutes, 0, $month, $day, $nextYear);

			if( $dateEnd > 0 && $date > $dateEnd ) {
				break;
			}

			$dates[] = $date;
			$counter++;
		}

		return $dates;
	}



	/**
	 * Get a matching start date for special week day selectino
	 * Ex: Start day may be sunday, but series is every tuesday
	 *
	 * @param	integer		$dateStart
	 * @return	integer		Fixed start date (or input if this matched)
	 */
	private function getStartDateForWeekDay($dateStart) {
		$weekDayIndexes	= $this->getWeekDayIndexes();
		$startWeekDay	= date('w', $dateStart);

		if( in_array($startWeekDay, $weekDayIndexes) ) {
			return $dateStart;
		}

		for($i=1; $i<7; $i++) {
			$nextDate	= TodoyuTime::addDays($dateStart, $i);
			$weekDay	= date('w', $nextDate);

			if( in_array($weekDay, $weekDayIndexes) ) {
				return $nextDate;
			}
		}

		return $dateStart;
	}



	/**
	 * Get prepared fieldset for event
	 *
	 * @param	boolean		$newEvent
	 * @return	TodoyuFormFieldset
	 */
	protected function getFieldset($newEvent = false) {
		$seriesForm	= $this->getForm();
		$seriesForm->setUseRecordID(false);
		$fieldset	= $seriesForm->getFieldset('series');

		if( $newEvent || $this->hasNoFrequency() ) { // New event
			$fieldset->removeField('serieslabel');
			$fieldset->removeField('seriesinterval');
			$fieldset->removeField('seriesweekdays');
			$fieldset->removeField('seriesdate_end');
			$fieldset->removeField('seriesoverbooking');
		}

		if( $this->hasFrequencyWeekday() ) {
			$fieldset->removeField('seriesinterval', true);
		}
		if( !$this->hasFrequencyWeek() ) {
			$fieldset->removeField('seriesweekdays', true);
		}

		return $fieldset;
	}



	/**
	 * Add series fields to event form
	 *
	 * @param	TodoyuForm		$eventForm
	 * @param	boolean			$newEvent
	 * @return	TodoyuForm
	 */
	public function addSeriesFields(TodoyuForm $eventForm, $newEvent = false) {
		$fieldset	= $this->getFieldset($newEvent);

		$eventForm->injectFieldset($fieldset, 'after:main');
		$eventForm->addHiddenField('serieseditfuture', 0);

		return $eventForm;
	}



	/**
	 * Get overbooking conflicts for series and injected event data
	 *
	 * @return	TodoyuCalendarOverbookingConflict[]
	 */
	public function getOverbookingConflicts() {
		$event	= TodoyuCalendarEventStaticManager::getEvent(0);

			// Blocking event type: get conflicts
		$event->injectData($this->eventData);
		$ranges	= $this->getNextRanges($event->getDateStart(), $event->getDuration());
		$users	= TodoyuArray::assure($this->eventData['persons']);

			// Check if users are data arrays or just a list of IDs
		if( is_array($users[0]) ) {
			$userIDs= TodoyuArray::getColumn($users, 'id');
		} else {
			$userIDs= TodoyuArray::intval($users, true, true);
		}

		return $this->getOverbookingConflictsForPersonsInRanges($ranges, $userIDs);
	}



	/**
	 * Get warning messages for overbooking conflicts
	 *
	 * @param	boolean		$fullRangeDate
	 * @return	string[]
	 */
	public function getOverbookingConflictsWarningMessages($fullRangeDate = false) {
		$overbookingConflicts	= $this->getOverbookingConflicts();
		$warningMessages		= array();

		foreach($overbookingConflicts as $overbookingConflict) {
			$warningMessages[] = $overbookingConflict->getWarningMessage($fullRangeDate);
		}

		return $warningMessages;
	}



	/**
	 * Get overbooking conflicts for persons in the given ranges
	 *
	 * @param	TodoyuDateRange[]	$ranges
	 * @param	integer[]			$personIDs
	 * @param	integer				$limit
	 * @return	TodoyuCalendarOverbookingConflict[]
	 */
	protected function getOverbookingConflictsForPersonsInRanges(array $ranges, array $personIDs, $limit = 20) {
		$personIDs	= TodoyuArray::intval($personIDs, true, true);
		$limit		= intval($limit);

		if( empty($ranges) || empty($personIDs) ) {
			return array();
		}

		$fields		= '	DISTINCT
						mmep.id_person,
						mmep.id_event';
		$tables		= ' ext_calendar_mm_event_person mmep
							LEFT JOIN ext_calendar_event e
								ON	mmep.id_event	= e.id
								AND	e.deleted		= 0';
		$where		= '		e.is_dayevent	= 0';
		$subWheres	= array();
		$order		= '	e.date_start ASC';
		$group		= '	mmep.id_person, mmep.id_event';

		if( $this->getID() > 0 ) {
			$tables .= ' AND e.id_series != ' . $this->getID();
		}

			// Prepare sub where statements
		foreach($personIDs as $idPerson) {
			foreach($ranges as $range) {
				$subWheres[] = '		mmep.id_person = ' . $idPerson
							. ' AND ('
							. '		e.date_start BETWEEN ' . ($range->getStart()+1) . ' AND ' . ($range->getEnd()-1)
							. ' OR	e.date_end BETWEEN ' . ($range->getStart()+1) . ' AND ' . ($range->getEnd()-1)
							. ' OR	(e.date_start <= ' . $range->getStart() . ' AND e.date_end >= ' . $range->getEnd() . ')'
							. '	)';
			}
		}

			// Add sub where conditions
		if( !empty($subWheres)  ) {
			$where .= ' AND (' . implode(') OR (', $subWheres) . ')';
		}

		$conflictsData	= Todoyu::db()->getArray($fields, $tables, $where, $group, $order, $limit);
		$conflicts		= array();

//		TodoyuDebug::printLastQueryInFirebug();

			// Build list of overbooking conflicts
		foreach($conflictsData as $conflictData) {
			$conflicts[] = new TodoyuCalendarOverbookingConflict($conflictData['id_event'], $conflictData['id_person']);
		}

		return $conflicts;
	}



	/**
	 * Get next valid start date for series
	 *
	 * @param	integer		$date
	 * @return	integer
	 */
	public function getFixedStartDate($date) {
		switch( $this->getFrequency() ) {
			case CALENDAR_SERIES_FREQUENCY_DAY:
			case CALENDAR_SERIES_FREQUENCY_MONTH:
			case CALENDAR_SERIES_FREQUENCY_YEAR:
					$dateStart	= $date;
					break;
			case CALENDAR_SERIES_FREQUENCY_WEEK:
				$dateStart	= $this->getNextWeekDay($date);
				break;
			case CALENDAR_SERIES_FREQUENCY_WEEKDAY:
				$dateStart	= $this->getNextWorkingDay($date);
				break;
			default:
				$dateStart	= $date;
				break;
		}

		return $dateStart;
	}



	/**
	 * Get next date which is in the group of selected week days
	 * Ex: Next monday or tuesday
	 *
	 * @param	integer		$date
	 * @return	integer
	 */
	private function getNextWeekDay($date) {
		$weekDays	= $this->getWeekDayIndexes();

		for($i=0; $i<7; $i++) {
			$day		= TodoyuTime::addDays($date, $i);
			$weekDay	= date('w', $day);
			if( in_array($weekDay, $weekDays) ) {
				return $day;
			}
		}

		return $date;
	}



	/**
	 * Get next date which is a working day in week
	 *
	 * @param	integer		$date
	 * @return	integer
	 */
	private function getNextWorkingDay($date) {
		$weekEndDays	= TodoyuTime::getWeekEndDayIndexes();

		for($i=0; $i<7; $i++) {
			$day		= TodoyuTime::addDays($date, $i);
			$weekDay	= date('w', $day);
			if( !in_array($weekDay, $weekEndDays) ) {
				return $day;
			}
		}

		return $date;
	}



	/**
	 * Create events which are based on this series and the base event
	 * Starting from start date
	 *
	 * @param	integer		$idBaseEvent
	 * @param	integer		$dateStart
	 * @param	boolean		$includeStartDate
	 * @return	integer[]
	 */
	public function createEvents($idBaseEvent, $dateStart, $includeStartDate = false) {
		$idBaseEvent		= intval($idBaseEvent);
		$baseEvent			= TodoyuCalendarEventStaticManager::getEvent($idBaseEvent);
		$baseEventData		= $baseEvent->getObjectData();
		$duration			= $baseEvent->getDuration();
		TodoyuCache::disable();
		$assignedPersonIDs	= $baseEvent->getAssignedPersonIDs();
		TodoyuCache::enable();
		$dateStart			= $this->getFixedStartDate($dateStart);
		
			// Get next start dates
		$nextStartDates	= $this->getNextStartDates($dateStart);
		$eventIDs	= array();

			// Create an event on start date?
		if( $includeStartDate ) {
			$nextStartDates[] = $dateStart;
		}

			// Create events for all found dates (copies of base event)
		foreach($nextStartDates as $nextStartDate) {
			$newEventData	= $baseEventData;
			$newEventData['id_series']	= $this->getID();
			$newEventData['date_start']	= $nextStartDate;
			$newEventData['date_end']	= $nextStartDate + $duration;

			$idEvent	= TodoyuCalendarEventStaticManager::addEvent($newEventData);

			TodoyuCalendarEventSeriesManager::assignEvent($idBaseEvent, $idEvent, $assignedPersonIDs);

			TodoyuHookManager::callHook('calendar', 'event.save', array(
				$idEvent,
				array(
					'new'		=> true,
					'batch'		=> true,
					'series'	=> true
				)
			));

			$eventIDs[] = $idEvent;
		}

		return $eventIDs;
	}



	/**
	 * Get first event of series
	 *
	 * @return	TodoyuCalendarEventStatic
	 */
	public function getFirstEvent() {
		$fields	= 'id';
		$table	= 'ext_calendar_event';
		$where	= '		id_series	= ' . $this->getID()
				. ' AND deleted		= 0';
		$order	= 'date_start ASC';
		$limit	= 1;

		$idEvent= Todoyu::db()->getFieldValue($fields, $table, $where, '', $order, $limit);

		return TodoyuCalendarEventStaticManager::getEvent($idEvent);
	}



	/**
	 * @param array $formData
	 * @return int
	 */
	protected function getValueFromFormData(array $formData, $key) {
		if ( is_array($formData[$key]) ) {
			$frequency = intval($formData[$key][0]);
		} else {
			$frequency = intval($formData[$key]);
		}

		return $frequency;
	}
}

?>