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
 * Wizard step
 *
 * @package		Todoyu
 * @subpackage	Core
 * @abstract
 */
abstract class TodoyuWizardStep {

	/**
	 * Wizard which contains
	 *
	 * @var		TodoyuWizard
	 */
	protected $wizard;

	/**
	 * Step config
	 *
	 * @var	Array
	 */
	protected $config;

	/**
	 * Submitted data
	 *
	 * @var	Boolean
	 */
	protected $data = null;



	/**
	 * Initialize step
	 *
	 * @param	TodoyuWizard	$wizard
	 * @param	array			$config
	 */
	final public function __construct(TodoyuWizard $wizard, array $config) {
		$this->wizard	= $wizard;
		$this->config	= $config;

		$this->init();
	}



	/**
	 * Get name of the step
	 *
	 * @return	string
	 */
	public function getName() {
		return $this->config['step'];
	}



	/**
	 * Replacement for the constructor
	 *
	 */
	protected function init() {
		// Dummy
	}



	/**
	 * Get title of the step
	 *
	 * @return		string
	 */
	public function getTitle() {
		return Todoyu::Label($this->config['title']);
	}



	/**
	 * Render content for help frame
	 *
	 * @return	string
	 */
	public function getHelp() {
		return empty($this->config['help']) ? '' : Todoyu::Label($this->config['help']);
	}



	/**
	 * Get step description
	 *
	 * @return	string
	 */
	public function getInfo() {
		return empty($this->config['info']) ? '' : Todoyu::Label($this->config['info']);
	}



	/**
	 * Save step data
	 *
	 * @abstract
	 * @param	array		$data
	 * @return	boolean
	 */
	abstract public function save(array $data);



	/**
	 * Render content
	 *
	 * @abstract
	 * @return	string
	 */
	abstract public function getContent();

}

?>