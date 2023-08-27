<?php 
/**
 * Check right of current person to delete given bookmark
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array				$params
 * @return	string
 */
function smarty_compiler_isBookmarkRemoveAllowed($params) {
	return '<?php TodoyuBookmarkRights::isRemoveAllowed(' . $params['idBookmark'] . ', TodoyuBookmarkBookmarkManager::getTypeIndex(' . $params['typeKey'] . ')); ?>';
}
