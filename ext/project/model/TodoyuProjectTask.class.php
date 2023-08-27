<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2013, snowflake productions GmbH, Switzerland
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
 * Task object
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTask extends TodoyuBaseObject {

	/**
	 * Initialize task
	 *
	 * @param	integer		$idTask
	 */
	public function __construct($idTask) {
		parent::__construct($idTask, 'ext_project_task');
	}



	/**
	 * Get task title
	 *
	 * @return	string
	 */
	public function getTitle() {
		return $this->get('title');
	}



	/**
	 * Get description
	 *
	 * @return	string
	 */
	public function getDescription() {
		return $this->get('description');
	}



	/**
	 * Get full task title (company - project - task)
	 *
	 * @return	string
	 */
	public function getFullTitle() {
		return $this->getProject()->getFullTitle() . ' - ' . $this->getTitle();
	}



	/**
	 * Get title with task number
	 *
	 * @return	string
	 */
	public function getTitleWithTaskNumber() {
		return $this->getTaskNumber(true) . ': ' . $this->getTitle();
	}



	/**
	 * Get task label
	 *
	 * @param	boolean		$full		Full/long label
	 * @return	string
	 */
	public function getLabel($full = false ) {
		return $full ? $this->getFullTitle() : $this->getTitleWithTaskNumber();
	}



	/**
	 * Get task number of the task.
	 * The task number is a combination of the project ID and an incrementing task number per project
	 *
	 * @param	boolean		$full		True: project ID + task number (concatinated with a dot), FALSE: Only task number
	 * @return	string
	 */
	public function getTaskNumber($full = true) {
		if( $full ) {
			return $this->data['id_project'] . '.' . $this->data['tasknumber'];
		} else {
			return $this->data['tasknumber'];
		}
	}



	/**
	 * Check whether task is in given status
	 *
	 * @param	integer		$idStatus
	 * @return	boolean
	 */
	public function hasStatus($idStatus) {
		return $this->getStatus() === intval($idStatus);
	}



	/**
	 * Get task status
	 *
	 * @return	integer
	 */
	public function getStatus() {
		return intval($this->data['status']);
	}



	/**
	 * Get task status text (not label, just text value of the status)
	 *
	 * @return	string
	 */
	public function getStatusKey() {
		return TodoyuProjectTaskStatusManager::getStatusKey($this->getStatus());
	}



	/**
	 * Get label for status
	 *
	 * @return	string
	 */
	public function getStatusLabel() {
		return TodoyuProjectTaskStatusManager::getStatusLabel($this->getStatus());
	}



	/**
	 * Check whether task status is relevant to time exceeding
	 *
	 * @return	boolean
	 */
	public function isStatusTimeExceedingRelevant() {
		$relevantStatus	= Todoyu::$CONFIG['EXT']['project']['taskStatusTimeExceedingRelevant'];

		return in_array($this->getStatus(), $relevantStatus);
	}



	/**
	 * Check whether task has a parent task (or is in project root)
	 *
	 * @return	boolean
	 */
	public function hasParentTask() {
		return $this->getParentTaskID() !== 0;
	}



	/**
	 * Check whether the task has sub tasks
	 *
	 * @return	boolean
	 */
	public function hasSubtasks() {
		return TodoyuProjectTaskManager::hasSubTasks($this->getID());
	}



	/**
	 * Get parent task if available
	 *
	 * @return	TodoyuProjectTask
	 */
	public function getParentTask() {
		if( $this->hasParentTask() ) {
			return TodoyuProjectTaskManager::getTask($this->getParentTaskID());
		}

		return false;
	}



	/**
	 * Get parent task ID. May be 0 when task is in project root
	 *
	 * @return	integer
	 */
	public function getParentTaskID() {
		return $this->getInt('id_parenttask');
	}



	/**
	 * Get project ID
	 *
	 * @return	integer
	 */
	public function getProjectID() {
		return $this->getInt('id_project');
	}



	/**
	 * Get project array
	 *
	 * @return	Array
	 */
	public function getProjectArray() {
		return TodoyuProjectProjectManager::getProjectArray($this->getProjectID());
	}



	/**
	 * Get project object
	 *
	 * @return	TodoyuProjectProject
	 */
	public function getProject() {
		return TodoyuProjectProjectManager::getProject($this->getProjectID());
	}



	/**
	 * Get activity record
	 *
	 * @return	TodoyuProjectActivity
	 */
	public function getActivity() {
		return TodoyuProjectActivityManager::getActivity($this->getActivityID());
	}



	/**
	 * Get activity label
	 *
	 * @return	string
	 */
	public function getActivityLabel() {
		$activity	= $this->getActivity();

		return $activity['title'];
	}



	/**
	 * Get activity ID
	 *
	 * @return	integer
	 */
	public function getActivityID() {
		return intval($this->data['id_activity']);
	}



	/**
	 * Get task type (TASK_TYPE_TASK = 1, TASK_TYPE_CONTAINER = 2)
	 *
	 * @return	integer		Type constant
	 */
	public function getType() {
		return $this->getInt('type');
	}



	/**
	 * Get task type key
	 *
	 * @return	string		'task' / 'container'
	 */
	public function getTypeKey() {
		return $this->isTask() ? 'task' : 'container';
	}



	/**
	 * Get start date
	 *
	 * @return	integer
	 * @deprecated
	 */
	public function getStartDate() {
		return $this->getDateStart();
	}



	/**
	 * Get start date
	 *
	 * @return	integer
	 */
	public function getDateStart() {
		return $this->getInt('date_start');
	}



	/**
	 * Get end date
	 *
	 * @return	integer
	 * @deprecated
	 */
	public function getEndDate() {
		return $this->getDateEnd();
	}



	/**
	 * Get end date (or fallback to deadline if enabled
	 *
	 * @param	boolean		$fallbackDeadline
	 * @return	integer
	 */
	public function getDateEnd($fallbackDeadline = false) {
		$dateEnd	= $this->getInt('date_end');

		return $dateEnd === 0 && $fallbackDeadline ? $this->getDateDeadline() : $dateEnd;
	}



	/**
	 * Check whether date end is set
	 *
	 * @return	boolean
	 */
	public function hasDateEnd() {
		return $this->getInt('date_end') > 0;
	}



	/**
	 * Get deadline date
	 *
	 * @deprecated
	 * @see		getDateDeadline
	 * @return	integer
	 */
	public function getDeadlineDate() {
		return $this->getDateDeadline();
	}



	/**
	 * Get deadline date
	 *
	 * @return	integer
	 */
	public function getDateDeadline() {
		return $this->getInt('date_deadline');
	}



	/**
	 * Check whether date deadline is set
	 *
	 * @return	boolean
	 */
	public function hasDateDeadline() {
		return $this->getInt('date_deadline') !== 0;
	}



	/**
	 * Check whether the deadline is exceeded
	 *
	 * @return	boolean
	 * @deprecated
	 */
	public function isDeadlineExceeded() {
		return $this->isDateDeadlineExceeded();
	}



	/**
	 * Check whether the deadline is exceeded
	 *
	 * @return	boolean
	 */
	public function isDateDeadlineExceeded() {
		return $this->isDateExceeded($this->getDateDeadline(), TodoyuProjectExtConfViewHelper::getToleranceDateDeadline());
	}



	/**
	 * Check tasks in date-relevant status for end date being exceeded
	 *
	 * @return	boolean
	 * @deprecated
	 */
	public function isEndDateExceeded() {
		return $this->isDateEndExceeded();
	}



	/**
	 * Check whether date end is in the past
	 *
	 * @return	boolean
	 */
	public function isDateEndExceeded() {
		return $this->getDateEnd() === 0 ? false : $this->isDateExceeded($this->getDateEnd(), TodoyuProjectExtConfViewHelper::getToleranceDateEnd());
	}



	/**
	 * Check if given Date is Exceeded. Depends on task-type, status & if tolerance is set.
	 *
	 * @param	integer		$date
	 * @param	integer		$tolerance
	 * @return	boolean
	 */
	protected function isDateExceeded($date, $tolerance) {
		$exceeded = false;

		if( $this->isTask() && $this->isStatusTimeExceedingRelevant() && $tolerance > 0 ) {
			 $exceeded = $date + $tolerance < NOW;
		}

		return $exceeded;
	}



	/**
	 * Get estimated workload
	 *
	 * @return	integer
	 */
	public function getEstimatedWorkload() {
		return $this->getInt('estimated_workload');
	}



	/**
	 * Check whether the task has an estimated workload
	 *
	 * @return	boolean
	 */
	public function hasEstimatedWorkload() {
		return $this->getEstimatedWorkload() > 0;
	}



	/**
	 * Check whether the task is a container
	 *
	 * @return	boolean
	 */
	public function isContainer() {
		return $this->getType() === TASK_TYPE_CONTAINER;
	}



	/**
	 * Check whether the task is a normal task (no container or something else)
	 *
	 * @return	boolean
	 */
	public function isTask() {
		return $this->getType() === TASK_TYPE_TASK;
	}



	/**
	 * Check whether the task is marked as internal
	 *
	 * @return	boolean
	 */
	public function isPublic() {
		return $this->isFlagSet('is_public');
	}



	/**
	 * Check whether the task has already been acknowledged by the assigned person
	 *
	 * @return	boolean
	 */
	public function isAcknowledged() {
		return $this->isFlagSet('is_acknowledged');
	}



	/**
	 * Get assigned person
	 *
	 * @return	TodoyuContactPerson
	 */
	public function getPersonAssigned() {
		return $this->getPerson('assigned');
	}



	/**
	 * Get owner person
	 *
	 * @return	TodoyuContactPerson
	 */
	public function getPersonOwner() {
		return $this->getPerson('owner');
	}



	/**
	 * Get assigned person ID
	 *
	 * @return	integer
	 */
	public function getPersonAssignedID() {
		return $this->getPersonID('assigned');
	}



	/**
	 * Get owner person ID
	 *
	 * @return	integer
	 */
	public function getPersonOwnerID() {
		return $this->getPersonID('owner');
	}



	/**
	 * Get sorting position for project tree
	 *
	 * @return	integer
	 */
	public function getTreePosition() {
		return $this->getInt('sorting');
	}



	/**
	 * Check if current person is assigned to this task
	 *
	 * @return	boolean
	 */
	public function isCurrentPersonAssigned() {
		return TodoyuAuth::getPersonID() === $this->getPersonAssignedID();
	}



	/**
	 * Check whether task owner and creator is the same person
	 *
	 * @return	boolean
	 */
	public function isOwnerAndCreatorSame() {
		return $this->getPersonOwnerID() === $this->getPersonCreateID();
	}



	/**
	 * Check whether a person is assigned
	 *
	 * @return	boolean
	 */
	public function hasPersonAssigned() {
		return $this->getPersonAssignedID() !== 0;
	}



	/**
	 * Check whether an owner is defined
	 *
	 * @return	boolean
	 */
	public function hasOwnerPerson() {
		return $this->getPersonOwnerID() !== 0;
	}



	/**
	 * Check whether the task/container can be edited
	 * Check if task/container is not locked any user has edit rights
	 *
	 * @return	boolean
	 */
	public function isEditable($checkSubTasks = false) {
		if( $this->isLocked($checkSubTasks) ) {
			return false;
		}

		return TodoyuProjectTaskRights::isEditAllowed($this->getID());
	}



	/**
	 * Check whether the task can be deleted
	 *
	 * @return	boolean
	 */
	public function isDeletable() {
		$allowed	= TodoyuProjectTaskRights::isDeleteAllowed($this->getID());

		return $allowed && !$this->isLocked();
	}



	/**
	 * Check whether a task is locked
	 *
	 * @param	boolean		$checkSubTasks		Check also whether a subtask is locked (one subtask locked = task locked too)
	 * @return	boolean
	 */
	public function isLocked($checkSubTasks = false) {
		$isLocked	= TodoyuProjectTaskManager::isLocked($this->getID());

			// Not locked itself, check subtasks
		if( !$isLocked && $checkSubTasks ) {
			$isLocked = TodoyuProjectTaskManager::areSubtasksLocked($this->getID());
		}

		return $isLocked;
	}



	/**
	 * Check whether drag and drop of the task is allowed
	 *
	 * @return	boolean
	 */
	public function isDraggable() {
		$addTasksInOwnProjects	= Todoyu::allowed('project', 'addtask:addTaskInOwnProjects');
		$hasTaskEditRight		= TodoyuProjectTaskRights::hasStatusRight($this->getStatusKey(), 'edit');

		return $addTasksInOwnProjects && $hasTaskEditRight;
	}



	/**
	 * Check whether there are tabs configured for this type of task
	 *
	 * @return	boolean
	 */
	public function hasTabs() {
			// Check for configured tabs
		$type		= $this->getType() === TASK_TYPE_TASK ? 'task' : 'container';
		$hasTabs	= TodoyuContentItemTabManager::hasTabs('project', $type);

			// Call hooks
		$hasTabs	= TodoyuHookManager::callHookDataModifier('project', 'taskHasTabs', $hasTabs, array($this->getID()));

		return $hasTabs;
	}



	/**
	 * Get all IDs of the sub tasks
	 *
	 * @param	string|Boolean	$extraWhere
	 * @return	Array
	 */
	public function getAllSubTaskIDs($extraWhere = false) {
		return TodoyuProjectTaskManager::getAllSubTaskIDs($this->getID(), $extraWhere);
	}



	/**
	 * Load foreign data
	 *
	 */
	protected function loadForeignData($infoLevel) {
		if( $infoLevel >= 2 && !$this->has('project') ) {
			$this->set('project', $this->getProject()->getTemplateData());
			$this->set('person_create', $this->getPersonCreate()->getTemplateData());
			$this->set('person_assigned', $this->getPersonAssigned()->getTemplateData());
			$this->set('person_owner', $this->getPersonOwner()->getTemplateData());
			$this->set('activity', $this->getActivity()->getTemplateData());
			$this->set('fulltitle', $this->getFullTitle());
			$this->set('company', $this->getProject()->getCompany()->getTemplateData());
		}

		if( $infoLevel >= 1 && !$this->has('statuskey') ) {
			$this->set('statuskey', $this->getStatusKey());
			$this->set('statuslabel', $this->getStatusLabel());
		}
	}



	/**
	 * Get data for template rendering
	 *
	 * @param	integer		$infoLevel		Level of information (the higher the number, the more information is collected)
	 * @return	Array
	 */
	public function getTemplateData($infoLevel = 0) {
		$infoLevel	= intval($infoLevel);

		if( $infoLevel > 0 ) {
			$this->loadForeignData($infoLevel);
		}

		if( !$this->has('is_container') ) {
			$this->set('is_container', $this->isContainer());
			$this->set('is_locked', $this->isLocked());
			$this->set('isDraggable', $this->isDraggable());
		}

		return parent::getTemplateData();
	}

}

?>