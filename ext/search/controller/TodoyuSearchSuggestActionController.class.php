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
 * @subpackage	Search
 */
class TodoyuSearchSuggestActionController extends TodoyuActionController {

	/**
	 * Restrict access
	 *
	 * @param	array		$params
	 */
	public function init(array $params = array()) {
		Todoyu::restrict('search', 'general:use');
	}



	/**
	 * Get search results suggestion
	 *
	 * @param	array	$params
	 * @return	string
	 */
	public function suggestAction(array $params) {
		$query	= $params['query'];
		$mode	= $params['mode'];

		$suggestions	= TodoyuSearch::getSuggestions($query, $mode);

			// Display output
		return TodoyuSearchRenderer::renderSuggestions($suggestions);
	}

}

?>