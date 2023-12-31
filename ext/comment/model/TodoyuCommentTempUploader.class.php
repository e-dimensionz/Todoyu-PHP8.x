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
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentTempUploader extends TodoyuAssetsTempUploader {

	/**
	 * @param	integer		 $idComment
	 * @param	integer		$idTask
	 */
	public function __construct($idComment, $idTask) {
		$key	= intval($idComment) . '-' . intval($idTask);
		parent::__construct('comment', $key);
	}



	/**
	 * @param	string		$fileKey
	 * @return	Array
	 */
	public function getFileInfo($fileKey) {
		$pathToFileInfo = parent::getSessionKey() . '/files/' . $fileKey;
		return TodoyuArray::assure(TodoyuSession::get($pathToFileInfo));
	}

}

?>