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
 * Manager for IMAP account records
 *
 * @package		Todoyu
 * @subpackage	Imap
 */
class TodoyuImapImapAccountManager {

	/**
	 * @var	String		Default table for database requests
	 */
	const TABLE = 'ext_imap_account';



	/**
	 * Get IMAP account object
	 *
	 * @param	integer				$idAccount
	 * @return	TodoyuImapImapAccount
	 */
	public static function getAccount($idAccount) {
		return TodoyuRecordManager::getRecord('TodoyuImapImapAccount', $idAccount);
	}



	/**
	 * Get IMAP account objects for given IDs
	 *
	 * @param	integer[]				$accountIDs
	 * @return	TodoyuImapAccount[]
	 */
	public static function getAccountsByID(array $accountIDs) {
		$accountIDs	= TodoyuArray::intval($accountIDs);

		return TodoyuRecordManager::getRecordList('TodoyuImapImapAccount', $accountIDs);
	}



	/**
	 * Get all IMAP accounts from the database
	 *
	 * @return	Array	admin readable array of entries
	 */
	public static function getRecordsListingItems() {
		$items 			= array();
		$imapAccounts	= self::getAllAccounts(false);

		foreach($imapAccounts as $imapAccount) {
			$items[] = array(
				'id'	=> $imapAccount->getID(),
				'label'	=> $imapAccount->getLabel()
			);
		}

		return $items;
	}



	/**
	 * Get all (optionally only active ones) IMAP accounts
	 *
	 * @param	boolean					$onlyActive
	 * @return	TodoyuImapImapAccount[]
	 */
	public static function getAllAccounts($onlyActive = true) {
		$field	= 'id';
		$where	= 'deleted = 0';
		$order	= 'username,host';

		if( $onlyActive ) {
			$where .= ' AND is_active = 1';
		}

		$accountIDs	= Todoyu::db()->getColumn($field, self::TABLE, $where, '', $order);

		return TodoyuRecordManager::getRecordList('TodoyuImapImapAccount', $accountIDs);
	}



	/**
	 * Removes given IMAP account record from the database
	 *
	 * @param	integer	$idAccount
	 */
	public static function removeAccount($idAccount) {
		$idAccount	= intval($idAccount);

		TodoyuHookManager::callHook('imap', 'account.remove', array($idAccount));

		TodoyuRecordManager::deleteRecord(self::TABLE, $idAccount);
	}



	/**
	 * Save IMAP account record from given data
	 *
	 * @param	array		$data
	 * @return	integer
	 */
	public static function saveAccount(array $data) {
		$idAccount	= intval($data['id']);
		$xmlPath	= 'ext/imap/config/form/admin/account.xml';

		if( $idAccount === 0 ) {
			$idAccount = self::addAccount();
		}

			// Prepare data
		if( !empty($data['password']) ) {
			$data['password']	= TodoyuCrypto::encrypt($data['password']);
		} else {
			unset($data['password']);
		}

		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idAccount);

			// Update account record
		self::updateAccount($idAccount, $data);

		return $idAccount;
	}



	/**
	 * Add an account
	 *
	 * @param	array		$data
	 * @return	integer
	 */
	public static function addAccount(array $data = array()) {
		return TodoyuRecordManager::addRecord(self::TABLE, $data);
	}



	/**
	 * Update an account
	 *
	 * @param	integer		$idAccount
	 * @param	array		$data
	 * @return	boolean
	 */
	public static function updateAccount($idAccount, array $data) {
		return TodoyuRecordManager::updateRecord(self::TABLE, $idAccount, $data);
	}

}

?>