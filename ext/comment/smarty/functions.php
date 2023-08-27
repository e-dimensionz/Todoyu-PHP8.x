<?php

/**
 * Get perms for addin tasks
 * @param	int		$id
 * @return	boolean
 */
function fn_isAddInTaskAllowed($id) {
	$idTask	= intval($id);
	return TodoyuCommentRights::isAddInTaskAllowed($idTask);
}

/**
 * @param	array			$params
 * @return	string
 */
function smarty_compiler_linkComments($params) {
	return '<?php echo TodoyuCommentCommentManager::linkCommentIDsInText(' . $params['text'] . '); ?>';
}

/**
  * @return	boolean
 */
function smarty_function_isSeeAllCommentsAllowed() {
	return Todoyu::allowed('comment', 'comment:seeAll');
}

