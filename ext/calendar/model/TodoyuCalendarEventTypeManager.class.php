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
 * Calendar eventType manager
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarEventTypeManager {

	/**
	 * Add a new event type
	 *
	 * @param	integer		$index
	 * @param	string		$key
	 * @param	string		$label
	 */
	public static function addEventType($index, $key, $label) {
		$index	= intval($index);

		Todoyu::$CONFIG['EXT']['calendar']['eventtypes'][$index]	= array(
			'index'		=> $index,
			'key'		=> $key,
			'label'		=> $label
		);
	}



	/**
	 * Get event type data
	 *
	 * @param	string		$index
	 * @param	boolean		$parseLabel
	 * @return	Array
	 */
	public static function getEventType($index, $parseLabel = false) {
		$eventType	= TodoyuArray::assure(Todoyu::$CONFIG['EXT']['calendar']['eventtypes'][$index]);

		if( $parseLabel ) {
			$eventType['label']	= Todoyu::Label($eventType['label']);
		}

		return $eventType;
	}



	/**
	 * Get label of the event type
	 *
	 * @param	integer		$index
	 * @param	boolean		$parsed
	 * @return	string
	 */
	public static function getEventTypeLabel($index, $parsed = true) {
		$eventType	= self::getEventType($index);
		$label		= $eventType['label'];

		if( $parsed ) {
			$label	= Todoyu::Label($label);
		}

		return $label;
	}



	/**
	 * Get the key of an event type
	 *
	 * @param	integer		$index
	 * @return	string
	 */
	public static function getTypeKey($index) {
		$eventType	= self::getEventType($index);

		return $eventType['key'];
	}



	/**
	 * Get all event types
	 *
	 * @param	boolean		$parseLabels
	 * @return	Array
	 */
	public static function getEventTypes($parseLabels = false) {
		$eventTypes	= TodoyuArray::assure(Todoyu::$CONFIG['EXT']['calendar']['eventtypes']);

		foreach($eventTypes as $index => $eventType) {
			$eventTypes[$index]['value']	= $index;

			if( $parseLabels ) {
				$eventTypes[$index]['label']= Todoyu::Label($eventType['label']);
			}

			$eventTypes[$index]['class']	= 'eventtype_' . $eventType['key'];
		}

		return $eventTypes;
	}



	/**
	 * Get options array of event types
	 *
	 * @return	Array
	 */
	public static function getEventTypeOptions() {
		$eventTypes	= self::getEventTypes(true);

		return TodoyuArray::sortByLabel($eventTypes, 'label');
	}



	/**
	 * Get event types which are allowed to be overbooked
	 *
	 * @return	integer[]
	 */
	public static function getOverbookableTypeIndexes() {
			// Is overbooking allowed for ALL event types?
		if( TodoyuCalendarManager::isOverbookingAllowed() ) {
			return self::getEventTypeIndexes();
		}
			// Get event types explicitly allowed for overbooking
		return Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_OVERBOOKABLE'];
	}



	/**
	 * Check whether (all or) the given event type is overbookable
	 *
	 * @param	integer		$eventType
	 * @return	boolean
	 */
	public static function isOverbookable($eventType, $isDayEvent = false) {
			// Overbooking is generally allowed?
		if( TodoyuCalendarManager::isOverbookingAllowed() ) {
			return true;
		}

		if( $isDayEvent == true && ! in_array($eventType, Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_ABSENCE'])) {
			return true;
		}

			// Check given type
		return in_array($eventType, Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_OVERBOOKABLE']);
	}



	/**
	 * Get event types which are not allowed to be overbooked
	 *
	 * @return	Array
	 */
	public static function getNotOverbookableTypeIndexes() {
			// Are all types allowed to be overbooked?
		if(  TodoyuCalendarManager::isOverbookingAllowed() ) {
			return array();
		}

		$overbookableTypes			= self::getOverbookableTypeIndexes();
		$nonOverbookableTypeIndexes	= array();

		$allEventTypes		= self::getEventTypeKeys();
		foreach( $allEventTypes as $typeKey ) {
			$idType	= constant('EVENTTYPE_' . strtoupper($typeKey));
			if( ! in_array($idType, $overbookableTypes)  ) {
				$nonOverbookableTypeIndexes[]	= $idType;
			}
		}

		return $nonOverbookableTypeIndexes;
	}



	/**
	 * Get all event type indexes (numerical)
	 *
	 * @return	integer[]
	 */
	public static function getEventTypeIndexes() {
		$eventTypes	= self::getEventTypes(false);

		return TodoyuArray::getColumn($eventTypes, 'index');
	}



	/**
	 * Get event type keys (textual)
	 *
	 * @return	string[]
	 */
	public static function getEventTypeKeys() {
		$eventTypes	= self::getEventTypes(false);

		return TodoyuArray::getColumn($eventTypes, 'key');
	}

}

?>