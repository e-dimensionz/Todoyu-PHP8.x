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
 * Project preference manager
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectPreferences {

	/**
	 * Save a preference for project
	 *
	 * @param	string		$preference
	 * @param	string		$value
	 * @param	integer		$idItem
	 * @param	boolean		$unique
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 */
	public static function savePref($preference, $value, $idItem = 0, $unique = false, $idArea = 0, $idPerson = 0) {
		TodoyuPreferenceManager::savePreference(EXTID_PROJECT, $preference, $value, $idItem, $unique, $idArea, $idPerson);
	}



	/**
	 * Get a preference
	 *
	 * @param	string		$preference
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	boolean		$unserialize
	 * @param	integer		$idPerson
	 * @return	string
	 */
	public static function getPref($preference, $idItem = 0, $idArea = 0, $unserialize = false, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreference(EXTID_PROJECT, $preference, $idItem, $idArea, $unserialize, $idPerson);
	}



	/**
	 * Get all preferences of project
	 *
	 * @param	string		$preference
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 * @return	Array
	 */
	public static function getPrefs($preference, $idItem = 0, $idArea = 0, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreferences(EXTID_PROJECT, $preference, $idItem, $idArea, $idPerson);
	}



	/**
	 * Delete project preference
	 *
	 * @param	string		$preference
	 * @param	string		$value
	 * @param	integer		$idItem
	 * @param	integer		$idArea
	 * @param	integer		$idPerson
	 */
	public static function deletePref($preference, $value = null, $idItem = 0, $idArea = 0, $idPerson = 0) {
		TodoyuPreferenceManager::deletePreference(EXTID_PROJECT, $preference, $value, $idItem, $idArea, $idPerson);
	}



	/**
	 * Save visibility of the sub tasks of a task
	 *
	 * @param	integer		$idTask
	 * @param	boolean		$isVisible
	 * @param	integer		$idArea
	 */
	public static function saveSubTasksVisibility($idTask, $isVisible = true, $idArea = EXTID_PROJECT) {
		$idTask	= intval($idTask);
		$idArea	= intval($idArea);

		if( $isVisible ) {
			self::savePref('tasktree-subtasks', $idTask, 0, false, $idArea);
		} else {
			self::deletePref('tasktree-subtasks', $idTask, 0, $idArea);
		}
	}



	/**
	 * Get visible sub tasks
	 *
	 * @param	integer 	$idArea
	 * @return	integer[]
	 */
	public static function getVisibleSubTaskIDs($idArea = EXTID_PROJECT) {
		$idArea	= intval($idArea);

		return self::getPrefs('tasktree-subtasks', 0, $idArea);
	}



	/**
	 * Save expanded (displaying it's details) task status
	 *
	 * @param	integer		$idTask			Task ID
	 * @param	boolean		$expanded		Is task now expanded?
	 */
	public static function saveTaskExpandedStatus($idTask, $expanded = true) {
		$idTask	= intval($idTask);

		if( $idTask !== 0 ) {
			if( $expanded ) {
				self::savePref('task-expanded', $idTask);
			} else {
				self::deletePref('task-expanded', $idTask);
			}
		}
	}



	/**
	 * Get IDs of the expanded tasks
	 *
	 * @return	Array
	 */
	public static function getExpandedTaskIDs() {
		$taskIDs = self::getPrefs('task-expanded');

		return TodoyuArray::assure($taskIDs);
	}



	/**
	 * Save open project tabs (ID of the open projects)
	 *
	 * @param	array		$projectIDs
	 */
	public static function saveOpenProjectTabs(array $projectIDs = array()) {
		$projectIDs	= TodoyuArray::intval($projectIDs, true, true);
		$list		= implode(',', $projectIDs);

		self::savePref('projecttabs', $list, 0, true);
	}



	/**
	 * Save pref: details being expanded?
	 *
	 * @param	integer		$idProject
	 * @param	boolean		$expanded
	 */
	public static function saveExpandedDetails($idProject, $expanded = true) {
		$idProject	= intval($idProject);

		if( $expanded ) {
			self::savePref('detailsexpanded', 1, $idProject, true);
		} else {
			self::deletePref('detailsexpanded', null, $idProject);
		}
	}



	/**
	 * Check whether given project details are expanded
	 *
	 * @param	integer		$idProject
	 * @return	boolean
	 */
	public static function isProjectDetailsExpanded($idProject) {
		$idProject	= intval($idProject);

		return self::getPref('detailsexpanded', $idProject) == 1;
	}



	/**
	 * Get open project tabs
	 *
	 * @return	Array		IDs of the projects which are displayed as tabs
	 */
	public static function getOpenProjectIDs() {
		$list		= self::getPref('projecttabs');
		$projectIDs	= TodoyuArray::intExplode(',', $list, true, true);

			// Filter to keep only allowed projects
		return self::unsetForbiddenProjectIDs($projectIDs);
	}



	/**
	 * Remove projects which are not allowed to be seen from array of project IDs
	 *
	 * @param	array	$projectIDs
	 * @return	Array
	 */
	public static function unsetForbiddenProjectIDs($projectIDs = array()) {
		foreach($projectIDs as $index => $idProject) {
			if( ! TodoyuProjectProjectRights::isSeeAllowed($idProject) ) {
				unset($projectIDs[$index]);
			}
		}

		return array_slice($projectIDs, 0, TodoyuProjectExtConfViewHelper::getMaxNumberOfOpenProjects());
	}



	/**
	 * Add a new project to the open tab list
	 *
	 * @param	integer		$idProject
	 */
	public static function addOpenProject($idProject) {
		self::addOpenProjects(array($idProject));
	}



	/**
	 * @param	$openProjectIDs
	 */
	public static function addOpenProjects(array $openProjectIDs) {
		$openProjectIDs	= TodoyuArray::intval($openProjectIDs, true);

		TodoyuCache::disable();
			// Get currently tabbed projects
		$projectIDs	= self::getOpenProjectIDs();
		TodoyuCache::enable();

			// Remove project from list if already in
		$projectIDs	= TodoyuArray::removeByValue($projectIDs, $openProjectIDs);

			// Prepend the current one
		$projectIDs = array_slice(array_merge($openProjectIDs, $projectIDs), 0, TodoyuProjectExtConfViewHelper::getMaxNumberOfOpenProjects());

		self::saveOpenProjectTabs($projectIDs);
	}



	/**
	 * Remove a project from open list
	 *
	 * @param	integer		$idProject
	 */
	public static function removeOpenProject($idProject) {
		$idProject	= intval($idProject);

			// Get currently tabbed projects
		TodoyuCache::disable();
		$projectIDs	= self::getOpenProjectIDs();
		TodoyuCache::enable();

			// Remove project from list
		$projectIDs	= TodoyuArray::removeByValue($projectIDs, array($idProject));

		self::saveOpenProjectTabs($projectIDs);
	}



	/**
	 * Get active project ID
	 *
	 * @return	integer
	 */
	public static function getActiveProject() {
		$openProjects	= self::getOpenProjectIDs();
		$idProject		= 0;

		foreach($openProjects as $idOpenProject) {
			if( TodoyuProjectProjectRights::isSeeAllowed($idOpenProject) ) {
				$idProject	= $idOpenProject;
				break;
			}
		}

		return $idProject;
	}



	/**
	 * Get the key of the currently active tab inside the details of the given project (default if none is selected)
	 *
	 * @param	integer		$idProject
	 * @return	string
	 */
	public static function getActiveProjectDetailTab($idProject) {
		return TodoyuContentItemTabPreferences::getActiveTab('project', 'projectdetail', $idProject);
	}



	/**
	 * Get the key of the currently active tab (default if none is selected)
	 *
	 * @param	integer		$idTask
	 * @return	string
	 */
	public static function getActiveItemTab($idTask, $itemKey) {
		return TodoyuContentItemTabPreferences::getActiveTab('project', $itemKey, $idTask);
	}



	/**
	 * Save active tab in project
	 *
	 * @param	integer		$idProject
	 * @param	string		$tab
	 */
	public static function saveActiveProjectDetailTab($idProject, $tab) {
		TodoyuContentItemTabPreferences::saveActiveTab('project', 'projectdetail', $idProject, $tab);
	}



	/**
	 * Save active tab in task
	 *
	 * @param	integer		$idTask
	 * @param	string		$tab
	 */
	public static function saveActiveItemTab($idItem, $tab, $itemKey) {
		TodoyuContentItemTabPreferences::saveActiveTab('project', $itemKey, $idItem, $tab);
	}



	/**
	 * Set forced project detail tab for current rendering
	 *
	 * @param	string		$tab
	 */
	public static function setForcedProjectTab($tab) {
		TodoyuContentItemTabPreferences::setForcedTab('project', 'projectdetail', $tab);
	}



	/**
	 * Set forced task tab for current rendering
	 *
	 * @param	string		$tab
	 */
	public static function setForcedTaskTab($tab) {
		TodoyuContentItemTabPreferences::setForcedTab('project', 'task', $tab);
	}



	/**
	 * Get currently forced project detail tab (or false)
	 *
	 * @return	string		Or FALSE
	 */
	public static function getForcedProjectDetailTab() {
		return TodoyuContentItemTabPreferences::getForcedTab('project', 'projectdetail');
	}



	/**
	 * Get currently forced task tab (or false)
	 *
	 * @return	string		Or FALSE
	 */
	public static function getForcedTaskTab() {
		return TodoyuContentItemTabPreferences::getForcedTab('project', 'task');
	}
}

?>