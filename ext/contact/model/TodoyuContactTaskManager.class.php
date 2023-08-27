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
 * Manage tasks for contact
 *
 * @package		Todoyu
 * @subpackage	Contact
 */
class TodoyuContactTaskManager {

	/**
	 * Extend task data attributes to implement person quickInfos
	 *
	 * @param	array	$data
	 * @param	integer	$idTask
	 * @return	Array
	 */
	public static function hookModifyTaskPersonAttributes(array $data, $idTask) {
		$idTask		= intval($idTask);
		$task		= TodoyuProjectTaskManager::getTask($idTask);

		$personTypes	= array('create', 'owner', 'assigned');
		foreach($personTypes as $type) {
			if( isset($data['person_' . $type]) ) {
                if(empty($data['person_' . $type]['wrap'])) $data['person_' . $type]['wrap'] = [];
                if(empty($data['person_' . $type]['wrap'][1])) $data['person_' . $type]['wrap'][1] = '';
                if(empty($data['person_' . $type]['wrap'][1])) $data['person_' . $type]['wrap'][0] = '';
                if(empty($data['person_' . $type]['className'])) $data['person_' . $type]['className'] = '';

				$idPerson	= $task->getPersonID($type);

				if( $idPerson !== 0 ) {
					$htmlID		= 'task_person' . $type . '-' . $idTask . '-' . $idPerson;

					$data['person_' . $type]['id']			= $htmlID;
					$data['person_' . $type]['wrap'][1]		.= TodoyuString::wrapScript('Todoyu.Ext.contact.QuickInfoPerson.add(\'' .  $htmlID . '\');');
					$data['person_' . $type]['className']	.= ' quickInfoPerson';

                    if(empty($data['person_' . $type]['classNameLabel'])) $data['person_' . $type]['classNameLabel'] = '';
					$data['person_' . $type]['classNameLabel']	.= ' personLabel';
					if( strpos($data['person_' . $type]['className'], 'sectionStart') !== false ) {
						$data['person_' . $type]['classNameLabel'] .= ' sectionStart';
					}
				}
			}
		}

		return $data;
	}

}

?>