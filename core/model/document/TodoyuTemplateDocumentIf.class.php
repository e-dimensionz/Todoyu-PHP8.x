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
 * [Enter Class Description]
 * 
 * @package		Todoyu
 * @subpackage	[Subpackage]
 */
interface TodoyuTemplateDocumentIf {

	/**
	 * Create a document object
	 *
	 * @param	array		$data		Template data
	 * @param	string		$template	Path the the template file (false, if the type doesn't need a template)
	 * @param	array		$config		Optional configuration passed to the document object
	 */
	public function __construct(array $data, $template, array $config = array());

	/**
	 * Save parsed document as a file
	 *
	 * @param	string		$savePath
	 */
	public function saveFile($savePath);


	/**
	 * Get the content of the new document (full code as string)
	 *
	 * @return	string
	 */
	public function getFileData();



	/**
	 * Get the path to the current temporary file
	 *
	 * @return	string
	 */
	public function getFilePath();



	/**
	 * Send the file to the browser (forces download)
	 *
	 * @param	string		$filename
	 */
	public function sendFile($filename);



	/**
	 * Get the document type
	 *
	 * @return	string
	 */
	public function getType();

}

?>