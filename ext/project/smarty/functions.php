<?php
/**
 * Ext specific Smarty functions
 *
 * @package		Todoyu
 * @subpackage	Template
 */
function fn_isAllowedSeeProjectDetails($idProject) {
	return Todoyu::allowed('project', 'project:seeAll') || TodoyuProjectProjectManager::isPersonAssigned($idProject);
}



/**
 * Get project status label
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array		$params
 * @return	string
 */
function smarty_function_projectStatusLabel($params) {
	$idStatus	= intval($params['idStatus']);

	return TodoyuProjectProjectStatusManager::getStatusLabel($idStatus);
}



/**
 * Get label of given project role
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array		$params
 * @return	string
 */
function smarty_function_projectRoleLabel($params) {
	return TodoyuProjectProjectroleManager::getLabel($params['idProjectrole']);
}



/**
 * Get project status key
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array	$params
 * @return	string
 */
function smarty_function_projectStatusKey($params) {
	$idStatus	= intval($params['idStatus']);

	return TodoyuProjectProjectStatusManager::getStatusKey($idStatus);
}

/**
 * @param	array			$params
 * @return	string
 */
function smarty_compiler_linkTasks($params) {
	return '<?php echo TodoyuProjectTaskManager::linkTaskIDsInText(' . $params['text'] . '); ?>';
}


/**
 * @param	integer		$idTask
 * @return	string
 */
function fn_taskNumber($idTask) {
	$idTask	= intval($idTask);
	return TodoyuProjectTaskManager::getTask($idTask)->getTaskNumber(true);
}


function smarty_function_taskStatusLabel($params, $smarty) {
	$idStatus	= intval($params['idStatus']);

	return TodoyuProjectTaskStatusManager::getStatusLabel($idStatus);
}

/**
 * Smarty plugin function for Header rendering
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @param	Smarty_Internal_template			$smarty		Smarty object
 * @return	string
 */

 function smarty_function_taskStatusKey($params, $smarty) {
	$idStatus	= intval($params['idStatus']);

	return TodoyuProjectTaskStatusManager::getStatusKey($idStatus);
}
