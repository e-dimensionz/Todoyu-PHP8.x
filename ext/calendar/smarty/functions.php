<?php 
/**
 * Ext specific Smarty functions
 *
 * @package		Todoyu
 * @subpackage	Template
 */
function smarty_compiler_EventTypeLabel($params) {
	return '<?php echo TodoyuCalendarEventTypeManager::getEventTypeLabel(' . $params['idEventIndex'] . '); ?>';
}


/**
 * Check right of current person to see given event
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	integer		$idEvent
 * @return	boolean
 */
function fn_isAllowedSeeEvent($idEvent) {
	return TodoyuCalendarEventRights::isSeeAllowed($idEvent);
}



/**
 * Check right of current person to add events
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @return	boolean
 */
function fn_isAllowedAddEvent() {
	return TodoyuCalendarEventRights::isAddAllowed();
}



/**
 * Check right of current person to edit given event
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	integer		$idEvent
 * @return	boolean
 */
function fn_isAllowedEditEvent($idEvent) {
	return TodoyuCalendarEventRights::isEditAllowed($idEvent);
}



/**
 * Get full label of event
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	integer		$idEvent
 * @return	string
 */
function fn_EventFullLabel($idEvent) {
	return TodoyuCalendarEventStaticManager::getEventFullLabel($idEvent);
}


/**
 * Get short name label of day name, e.g: 'Mon'
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param		integer		$timestamp
 * @return		string
 */
function fn_weekdayName($timestamp) {
	$timestamp	= intval($timestamp);

	return Todoyu::Label('core.date.weekday.' . strtolower(date('l', $timestamp)));
}


/**
 * Get short name label of day name, e.g: 'Mon'
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	integer		$timestamp
 * @return	string
 */
function fn_weekdayNameShort($timestamp) {
	$timestamp	= intval($timestamp);

	return Todoyu::Label('core.date.weekday.' . strtolower(date('D', $timestamp)));
}



/**
 * Check right of current person to schedule popup reminder of given event
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	integer		$idEvent
 * @return	boolean
 */
function fn_isAllowedEventReminderPopup($idEvent) {
	return TodoyuCalendarEventReminderRights::isPopupSchedulingAllowed($idEvent);
}



/**
 * Check right of current person to schedule email reminder of given event
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	integer		$idEvent
 * @return	boolean
 */
function fn_isAllowedEventReminderEmail($idEvent) {
	return TodoyuCalendarEventReminderRights::isEmailSchedulingAllowed($idEvent);
}
