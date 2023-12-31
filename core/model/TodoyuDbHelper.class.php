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
 * Various database helper functions
 * Usefull database operations used in several extensions
 *
 * @package		Todoyu
 * @subpackage	Core
 */
class TodoyuDbHelper {

	/**
	 * Save MM relations from 1 record to n others
	 * @deprecated
	 *
	 * @param	string		$mmTable			Link table
	 * @param	string		$fieldLocal			Locale field name (for the one record linked to the others)
	 * @param	string		$fieldForeign		Foreign field name for the other records
	 * @param	integer		$idLocalRecord		The linking record
	 * @param	array		$foreignRecordIDs	The other linked records
	 */
	public static function addMMLinks($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord, array $foreignRecordIDs) {
		$idLocalRecord		= (int) $idLocalRecord;
		$foreignRecordIDs	= TodoyuArray::intval($foreignRecordIDs, true, true);

			// Get existing record IDs in link table
		$currentForeignIDs	= self::getForeignIDs($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord);

			// If record not already linked, add link
		foreach($foreignRecordIDs as $idForeignRecord) {
			if( ! in_array($idForeignRecord, $currentForeignIDs) ) {
				self::addMMLink($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord, $idForeignRecord);
			}
		}
	}



	/**
	 * Save MM relations from 1 record to n others, with an enumerated sorting index.
	 *
	 * @param	string		$mmTable			Link table
	 * @param	string		$fieldLocal			Locale field name (for the one record linked to the others)
	 * @param	string		$fieldForeign		Foreign field name for the other records
	 * @param	integer		$idLocalRecord		The linking record
	 * @param	array		$foreignRecordIDs	The other linked records
	 */
	public static function saveMMLinksSorted($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord, array $foreignRecordIDs) {
		$idLocalRecord		= (int) $idLocalRecord;
		$foreignRecordIDs	= TodoyuArray::intval($foreignRecordIDs, true, true);

			// Remove existing link records
		self::removeMMrelations($mmTable, $fieldLocal, $idLocalRecord);

			// Add links
		$counter	= 0;
		foreach($foreignRecordIDs as $idForeignRecord) {
			$extraData = array('sorting'	=> $counter);
			self::addMMLink($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord, $idForeignRecord, $extraData);
			$counter++;
		}
	}



	/**
	 * Get foreign record IDs of a mm table
	 *
	 * @param	string		$mmTable
	 * @param	string		$fieldLocal
	 * @param	string		$fieldForeign
	 * @param	integer		$idLocalRecord
	 * @return	Array
	 */
	public static function getForeignIDs($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord) {
		$where	= $fieldLocal . ' = ' . (int) $idLocalRecord;

		return Todoyu::db()->getColumn($fieldForeign, $mmTable, $where);
	}



	/**
	 * Get foreign record IDs of an mm table, sorted by given column
	 *
	 * @param	string		$mmTable
	 * @param	string		$fieldLocal
	 * @param	string		$fieldForeign
	 * @param	integer		$idLocalRecord
	 * @param	string		$fieldSorting
	 * @return	Array
	 */
	public static function getForeignIDsSorted($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord, $fieldSorting = 'sorting') {
		$where	= $fieldLocal . ' = ' . (int) $idLocalRecord;

		return Todoyu::db()->getColumn($fieldForeign, $mmTable, $where, '', $fieldSorting);
	}



	/**
	 * Save MM relation with extended (more than the commonly two) data columns
	 *
	 * @param	string		$mmTable
	 * @param	string		$fieldLocal
	 * @param	string		$fieldForeign
	 * @param	integer		$idLocalRecord
	 * @param	array		$linksData
	 * @return	integer		New ID of the record
	 */
	public static function saveExtendedMMLinks($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord, array $linksData) {
		$idLocalRecord		= (int) $idLocalRecord;
		$foreignRecordIDs	= self::getForeignIDs($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord);

		foreach($linksData as $linkData) {
			if( in_array($linkData[$fieldForeign], $foreignRecordIDs) ) {
				self::updateMMLink($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord, $linkData[$fieldForeign], $linkData);
			} else {
				self::addMMLink($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord, $linkData[$fieldForeign], $linkData);
			}

		}
	}



	/**
	 * Update a mm link with extra data
	 *
	 * @param	string		$mmTable
	 * @param	string		$fieldLocal
	 * @param	string		$fieldForeign
	 * @param	integer		$idLocalRecord
	 * @param	integer		$idForeignRecord
	 * @param	array		$data
	 * @return	integer							Num affected rows
	 */
	public static function updateMMLink($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord, $idForeignRecord, array $data) {
		$where	=	$fieldLocal . ' = ' . $idLocalRecord . ' AND ' .
					$fieldForeign . '	= ' . $idForeignRecord;

		unset($data['id']);
		unset($data[$fieldLocal]);
		unset($data[$fieldForeign]);

		return Todoyu::db()->doUpdate($mmTable, $where, $data);
	}



	/**
	 * Add a single MM relation
	 *
	 * @param	string		$mmTable			Link table
	 * @param	string		$fieldLocal			Locale field name (for the one record linked to the others)
	 * @param	string		$fieldForeign		Foreign field name for the other records
	 * @param	integer		$idLocalRecord
	 * @param	integer		$idForeignRecord
	 * @param	array		$extraData
	 * @return	integer		ID of new record
	 */
	public static function addMMLink($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord, $idForeignRecord, array $extraData = array()) {
		$data	= array(
			$fieldLocal		=> (int) $idLocalRecord,
			$fieldForeign	=> (int) $idForeignRecord
		);

		unset($extraData['id']);
		unset($extraData[$fieldLocal]);
		unset($extraData[$fieldForeign]);

		$data	= array_merge($extraData, $data);

		return Todoyu::db()->addRecord($mmTable, $data);
	}



	/**
	 * Delete given MM relation
	 *
	 * @param	string	$mmTable
	 * @param	string	$localField
	 * @param	string	$foreignField
	 * @param	integer	$idLocalRecord
	 * @param	integer	$idForeignRecord
	 * @return	integer		Num affected (deleted) rows
	 */
	public static function removeMMrelation($mmTable, $localField, $foreignField, $idLocalRecord, $idForeignRecord) {
		$idLocalRecord	= (int) $idLocalRecord;
		$idForeignRecord= (int) $idForeignRecord;

		$where	=	TodoyuSql::backtick($localField) . ' = ' . $idLocalRecord . ' AND ' .
					TodoyuSql::backtick($foreignField) . ' = ' . $idForeignRecord;
		$limit	= 1;

		return Todoyu::db()->doDelete($mmTable, $where, $limit);
	}



	/**
	 * Remove given MM relation records
	 *
	 * @param	string		$mmTable
	 * @param	string		$field
	 * @param	integer		$idRecord
	 * @return	integer		Num affected (deleted) rows
	 */
	public static function removeMMrelations($mmTable, $field, $idRecord) {
		$idRecord	= (int) $idRecord;
		$where		= TodoyuSql::backtick($field) . ' = ' . $idRecord;

		return Todoyu::db()->doDelete($mmTable, $where);
	}



	/**
	 * Delete all records which are linked with another record and not in the ignore list
	 * Inactive records will be marked as deleted, but stay in the link table for restore
	 *
	 * @param	string		$mmTable			Link table
	 * @param	string		$recordTable		Record table
	 * @param	integer		$idLinkRecord		ID of the base record which is linked with the other records
	 * @param	array		$ignoreRecordIDs	IDs of records which shoudn't get deleted
	 * @param	string		$fieldRecord		Field with the base record ID
	 * @param	string		$fieldLink			Field with the linked record IDs
	 * @return	integer		Number of delete records
	 */
	public static function deleteOtherMmRecords($mmTable, $recordTable, $idLinkRecord, array $ignoreRecordIDs, $fieldRecord, $fieldLink) {
		$idLinkRecord	= (int) $idLinkRecord;
		$ignoreRecordIDs= TodoyuArray::intval($ignoreRecordIDs, true, true);
		$recordTable	= TodoyuSql::quoteTablename($recordTable);
		$mmTable		= TodoyuSql::quoteTablename($mmTable);
		$fieldRecord	= TodoyuSql::quoteFieldname('mm.' . $fieldRecord);
		$fieldLink		= TodoyuSql::quoteFieldname('mm.' . $fieldLink);

		$tables		=	$recordTable . ' r,' .
						$mmTable . ' mm';
		$where		= '		' . $fieldRecord . ' = ' . $idLinkRecord .
					  ' AND	' . $fieldLink . '	= r.id';

		if( sizeof($ignoreRecordIDs) > 0 ) {
			$where .= ' AND	r.id NOT IN(' . implode(',', $ignoreRecordIDs) . ')';
		}

		$update		= array(
			'r.deleted'	=> 1
		);
		$noQuotes	= array(
			'r.deleted'
		);

		return Todoyu::db()->doUpdate($tables, $where, $update, $noQuotes);
	}



	/**
	 * Delete all links whichs foreign field ID is not in the ignore list
	 * Deletes all inactive links. The row is deleted from the mm table
	 *
	 * @param	string		$mmTable
	 * @param	string		$fieldLocal
	 * @param	string		$fieldForeign
	 * @param	integer		$idLocalRecord
	 * @param	array		$ignoreForeignIDs
	 */
	public static function deleteOtherMmLinks($mmTable, $fieldLocal, $fieldForeign, $idLocalRecord, array $ignoreForeignIDs = array()) {
		$ignoreForeignIDs	= TodoyuArray::intval($ignoreForeignIDs, true, true);
		$idLocalRecord		= (int) $idLocalRecord;

		$where	= $fieldLocal . ' = ' . $idLocalRecord;

			// If a list of IDs to ignore is given, add ignore clause
		if( sizeof($ignoreForeignIDs) > 0 ) {
			$where .= ' AND	' . $fieldForeign . ' NOT IN(' . implode(',', $ignoreForeignIDs) . ')';
		}

		Todoyu::db()->doDelete($mmTable, $where);
	}



	/**
	 * Save items with new sorting value in their sorting fields
	 *
	 * @param	string		$table
	 * @param	array		$itemIDs
	 * @param	string		$sortingField
	 * @param	string		$where
	 */
	public static function saveItemSorting($table, array $itemIDs, $sortingField = 'sorting',  $where = '') {
		$itemIDs= TodoyuArray::intval($itemIDs);
		$idList	= implode(',', $itemIDs);

		if( $where != '' ) {
			$where .= ' AND';
		}

		$where .= ' id IN(' . $idList . ')';

		$values	= array(
			$sortingField => 'FIELD(id,' . $idList . ')'
		);

		Todoyu::db()->doUpdate($table, $where, $values, array($sortingField));
	}

}
?>