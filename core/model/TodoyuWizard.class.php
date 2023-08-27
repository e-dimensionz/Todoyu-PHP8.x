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
 * Wizard base class
 *
 * @package		Todoyu
 * @subpackage	Core
 * @abstract
 */
abstract class TodoyuWizard {

	/**
	 * Wizard name
	 *
	 * @var	String
	 */
	private $name;

	/**
	 * List with created step instances
	 *
	 * @var	Array
	 */
	private $steps = array();



	/**
	 * @param	string		$name
	 */
	public function __construct($name) {
		$this->name	= $name;
	}



	/**
	 * Save given step data
	 *
	 * @param	string	$stepName
	 * @param	array	$data
	 * @return	TodoyuWizardStep
	 */
	public function save($stepName, array $data) {
		return $this->getStep($stepName)->save($data);
	}



	/**
	 * Render step of the wizard
	 *
	 * @param	boolean|String		$stepName
	 * @return	string
	 */
	public function render($stepName = false) {
		if( ! empty($stepName) ) {
			$this->setActiveStep($stepName);
		}

		$step		= $this->getActiveStep();

		$tmpl	= 'core/view/wizard.tmpl';
		$data	= array(
			'wizardName'	=> $this->name,
			'steps'			=> $this->getStepItems(),
			'stepName'		=> $step->getName(),
			'title'			=> $step->getTitle(),
			'content'		=> $step->getContent(),
			'info'			=> $step->getInfo(),
			'help'			=> $step->getHelp(),
			'isFirst'		=> $this->isFirstStep(),
			'isLast'		=> $this->isLastStep(),
			'doneSteps'		=> $this->getDoneStepNames()
		);

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Check whether current step is first step
	 *
	 * @return	boolean
	 */
	protected function isFirstStep() {
		return $this->getLastStepName() === false;
	}



	/**
	 * Check whether current step is last step
	 *
	 * @return	boolean
	 */
	protected function isLastStep() {
		return $this->getNextStepName() === false;
	}



	/**
	 * Get names of steps which are located before current
	 *
	 * @return	Array
	 */
	protected function getDoneStepNames() {
		$activeStep	= $this->getActiveStepName();

		$steps	= $this->getSteps();
		$done	= array();

		foreach($steps as $step) {
			if( $step['step'] == $activeStep ) {
				break;
			}
			$done[] = $step['step'];
		}

		return $done;
	}



	/**
	 * Get steps of the wizard
	 *
	 * @return	Array
	 */
	protected function getSteps() {
		return TodoyuWizardManager::getSteps($this->name);
	}



	/**
	 * Get all step items with rendered label
	 *
	 * @return	Array
	 */
	protected function getStepItems() {
		$steps	= TodoyuWizardManager::getSteps($this->name);

		return $steps;
	}



	/**
	 * Get step object
	 *
	 * @param	string				$stepName
	 * @return	TodoyuWizardStep
	 * @throws	TodoyuException
	 */
	protected function getStep($stepName) {
		$stepName	= $this->getActiveStepName($stepName);

		if( ! isset($this->steps[$stepName]) ) {
			$stepConfig	= TodoyuWizardManager::getStep($this->name, $stepName);

			$class	= get_class($this) . 'Step' . ucfirst(strtolower($stepName));

			if( class_exists($class, true) ) {
				 $this->steps[$stepName] = new $class($this, $stepConfig);
			} else {
				throw new TodoyuException('Wizard step class not found: ' . $class);
			}
		}

		return $this->steps[$stepName];
	}



	/**
	 * Get name of active step. Use parameter if step name was given
	 *
	 * @param	string	$stepName
	 * @return	Mixed|null|void
	 */
	protected function getActiveStepName($stepName = null) {
		if( empty($stepName) ) {
			$stepName = TodoyuWizardManager::getCurrentStep($this->name);
		}
		if( empty($stepName) ) {
			$stepName =	$this->getFirstStep();
		}

		return $stepName;
	}



	/**
	 * @return	TodoyuWizardStep
	 */
	public function getActiveStep() {
		return $this->getStep($this->getActiveStepName());
	}



	/**
	 * Get label of active step
	 *
	 * @return	string
	 */
	public function getActiveStepLabel() {
		return $this->getActiveStep()->getTitle();
	}



	/**
	 * Change step into given direction (next/back)
	 *
	 * @param	string		$direction
	 * @return	string		Next step key
	 */
	public function goToStepInDirection($direction) {
		if( $direction === 'next' ) {
			$this->goToNextStep();
		} elseif( $direction === 'back' ) {
			$this->goToLastStep();
		} elseif( $this->isStepName($direction) ) {
			$this->setActiveStep($direction);
		}

		return $this->getActiveStepName();
	}



	/**
	 * Change active step to the next one
	 *
	 * @return	string
	 */
	public function goToNextStep() {
		$nextStep	= $this->getNextStepName();

		$this->setActiveStep($nextStep);

		return $nextStep;
	}



	/**
	 * Go to step before current
	 *
	 * @return	string|Boolean
	 */
	public function goToLastStep() {
		$lastStep	= $this->getLastStepName();

		if( $lastStep !== false ) {
			$this->setActiveStep($lastStep);
		}

		return $lastStep;
	}



	/**
	 * Check whether the name is a valid step name
	 *
	 * @param	string		$stepName
	 * @return	boolean
	 */
	protected function isStepName($stepName) {
		$stepNames	= TodoyuArray::getColumn($this->getSteps(), 'step');

		return in_array($stepName, $stepNames);
	}



	/**
	 * Get next step
	 *
	 * @return	TodoyuWizardStep
	 */
	protected function getNextStep() {
		return $this->getStep($this->getNextStepName());
	}



	/**
	 * Get step before current
	 *
	 * @return	TodoyuWizardStep
	 */
	protected function getLastStep() {
		return $this->getStep($this->getLastStepName());
	}



	/**
	 * Set active step
	 *
	 * @param	string		$stepName
	 */
	protected function setActiveStep($stepName) {
		TodoyuWizardManager::setCurrentStep($this->name, $stepName);
	}



	/**
	 * Get name of next step
	 * False if no next step was found
	 *
	 * @return	string|Boolean
	 */
	protected function getNextStepName() {
		$activeStep	= $this->getActiveStepName();

		$steps		= TodoyuWizardManager::getSteps($this->name);
		$found		= false;

		foreach($steps as $step) {
			if( $found ) {
				return $step['step'];
			}
			if( $step['step'] == $activeStep ) {
				$found = true;
			}
		}

		return false;
	}



	/**
	 * Get name of last step
	 *
	 * @return	string|Boolean
	 */
	protected function getLastStepName() {
		$activeStep	= $this->getActiveStepName();

		$steps		= TodoyuWizardManager::getSteps($this->name);
		$last		= $steps[0]['step'];

		if( $activeStep === $last ) {
			return false;
		}

		foreach($steps as $step) {
			if( $step['step'] == $activeStep ) {
				return $last;
			}

			$last = $step['step'];
		}

		return false;
	}



	/**
	 * Get name of first step in the wizard
	 * This step is displayed, if no step is set
	 *
	 * @return	string
	 */
	abstract protected function getFirstStep();

}

?>