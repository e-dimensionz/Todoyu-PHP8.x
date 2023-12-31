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
 * Save button form element
 *
 * @package		Todoyu
 * @subpackage	Form
 */
class TodoyuFormElement_SaveButton extends TodoyuFormElement_Button {

	/**
	 * Initialize saveButton element
	 *
	 * @param	string			$name
	 * @param	TodoyuFormFieldset	$fieldset
	 * @param	array			$config
	 */
	public function __construct($name, TodoyuFormFieldset $fieldset, array $config = array()) {
		TodoyuFormElement::__construct('saveButton', $name, $fieldset, $config);
	}



	/**
	 * Init: Set default values for save button
	 */
	protected function init() {
		if( !$this->hasAttribute('text') ) {
			$this->setText('core.form.field.save');
		}
		if( !$this->hasAttribute('class') ) {
			$this->setClass('save');
		}

		parent::init();
	}

}

?>