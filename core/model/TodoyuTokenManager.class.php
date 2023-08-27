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
 * Token Manager
 *
 * @package		Todoyu
 * @subpackage	Core
 */
class TodoyuTokenManager {

	/**
	 * Database table
	 */
	const TABLE = 'system_token';



	/**
	 * Token Getter
	 *
	 * @param	integer			$idToken
	 * @return	TodoyuToken
	 */
	public static function getToken($idToken) {
		$idToken	= (int) $idToken;

		return TodoyuRecordManager::getRecord('TodoyuToken', $idToken);
	}



	/**
	 * Get ID of token of given extension, type and owner person
	 *
	 * @param	integer		$extID
	 * @param	integer		$idTokenType
	 * @param	integer		$idPerson
	 * @return	integer
	 */
	public static function getTokenIdByPerson($extID, $idTokenType, $idPerson = 0) {
		$extID			= (int) $extID;
		$idTokenType	= (int) $idTokenType;
		$idPerson		= Todoyu::personid($idPerson);

		$field	= 'id';
		$table	= self::TABLE;
		$where	= '		ext				= ' . $extID
				. ' AND	token_type		= ' . $idTokenType
				. ' AND	id_person_owner	= ' . $idPerson
				. ' AND deleted			= 0';

		return (int) Todoyu::db()->getFieldValue($field, $table, $where);
	}



	/**
	 * Get token of given extension and type by owner's person ID
	 *
	 * @param	integer		$extID
	 * @param	integer		$idTokenType
	 * @param	integer		$idPerson
	 * @return	TodoyuToken|Boolean
	 */
	public static function getTokenByPerson($extID, $idTokenType, $idPerson = 0) {
		$idToken	= self::getTokenIdByPerson($extID, $idTokenType, $idPerson);

		if( $idToken !== 0 ) {
			return self::getToken($idToken);
		}

		return false;
	}



	/**
	 * Get token with given hash
	 *
	 * @param	string		$hash
	 * @return	TodoyuToken|Boolean
	 */
	public static function getTokenByHash($hash) {
		;
		$hash	= mysqli_real_escape_string(Todoyu::db()->link, $hash);

		$where	= '	hash = \'' . $hash . '\' AND deleted = 0';
		$idToken= Todoyu::db()->getFieldValue('id', self::TABLE, $where);

		return $idToken !== false ? self::getToken($idToken) : false;
	}



	/**
	 * Generate new token hash
	 *
	 * @param	integer		$extID
	 * @param	integer		$idTokenType
	 * @param	integer		$idPerson
	 * @param	boolean		$storeInSession
	 * @return	string							Hash
	 */
	public static function generateHash($extID, $idTokenType, $idPerson = 0, $storeInSession = false) {
		$extID			= (int) $extID;
		$idTokenType	= (int) $idTokenType;
		$idPerson		= Todoyu::personid($idPerson);
		$idPersonCreate	= Todoyu::personid();

			// Generate new hash
		$prefix	= $extID . $idTokenType . $idPersonCreate . $idPerson;
		$salt	= uniqid($prefix, microtime(true));
		$hash	= md5($salt);

			// Ensure the hash not being used yet
		if( !self::isUnusedHash($hash) ) {
			$hash	= self::generateHash($extID, $idTokenType, $idPerson, $storeInSession);
		}

			// Cache the hash in the session
		if( $storeInSession ) {
			self::storeHashInSession($extID, $idTokenType, $hash);
		}

		return $hash;
	}



	/**
	 * Check given hash to (not) exist already
	 *
	 * @param	string	$hash
	 * @return	boolean
	 */
	public static function isUnusedHash($hash) {
		$token	= self::getTokenByHash($hash);

		return $token == false || $token->getID() == 0;
	}



	/**
	 * Store hash of given extension and type in session
	 *
	 * @param	integer		$idTokenType
	 * @param	integer		$extID
	 * @param	string		$hash
	 * @param	integer		$idPerson
	 */
	public static function storeHashInSession($extID, $idTokenType, $hash, $idPerson = 0) {
		$idTokenType= (int) $idTokenType;
		$extID		= (int) $extID;
		$idPerson	= Todoyu::personid($idPerson);
		$hashPath	= self::getTokenHashSessionPath($extID, $idTokenType, $idPerson);

		TodoyuSession::set($hashPath, $hash);
	}



	/**
	 * Get path to token hash value in session
	 *
	 * @param	integer		$extID
	 * @param	integer		$idTokenType
	 * @param	integer		$idPerson
	 * @return	string
	 */
	public static function getTokenHashSessionPath($extID, $idTokenType, $idPerson = 0) {
		$extID		= (int) $extID;
		$idTokenType= (int) $idTokenType;
		$idPerson	= Todoyu::personid($idPerson);

		return 'tokenHash/' . $extID . '/' . $idTokenType . '/' . $idPerson;
	}



	/**
	 * Get hash of given extension, type and owner from session
	 *
	 * @param	integer		$extID
	 * @param	integer		$idTokenType
	 * @param	integer		$idPerson
	 * @return	string
	 */
	public static function getHashFromSession($extID, $idTokenType, $idPerson = 0) {
		$idTokenType= (int) $idTokenType;
		$extID		= (int) $extID;
		$hashPath	= self::getTokenHashSessionPath($extID, $idTokenType, $idPerson);

		return TodoyuSession::get($hashPath);
	}



	/**
	 * Store token to database, hash value is taken from session
	 *
	 * @param	integer		$idPerson
	 * @param	integer		$extID
	 * @param	$idTokenType
	 */
	public static function saveTokenFromSession($extID, $idTokenType, $idPerson = 0) {
		$extID		= (int) $extID;
		$idTokenType= (int) $idTokenType;
		$idPerson	= Todoyu::personid($idPerson);
		$idToken	= self::getTokenIdByPerson($extID, $idTokenType, $idPerson);
		$hash		= self::getHashFromSession($extID, $idTokenType, $idPerson);

		$data	= array(
			'id'				=> $idToken,
			'ext'				=> $extID,
			'token_type'		=> $idTokenType,
			'id_person_owner'	=> $idPerson,
			'hash'				=> $hash
		);

		self::saveToken($data);
	}



	/**
	 * Save token to DB (new or update)
	 *
	 * @param	array	$data
	 * @return	integer			ID of token record
	 */
	public static function saveToken(array $data) {
		$idToken	= (int) $data['id'];

			// Is a new token?
		if( $idToken === 0 ) {
			$idToken = self::addToken($data);
		} else {
				// Is update of existing token
			self::updateToken($idToken, $data);
			self::removeTokenFromCache($idToken);
		}

		return $idToken;
	}



	/**
	 * Add new token record to DB
	 *
	 * @param	array		$data
	 * @return	integer				Token record ID
	 */
	public static function addToken(array $data = array()) {
		$data['date_create']		= NOW;
		$data['id_person_create']	= Todoyu::personid();

		return TodoyuRecordManager::addRecord(self::TABLE, $data);
	}



	/**
	 * Update token
	 *
	 * @param	integer		$idToken
	 * @param	array		$data
	 * @return	boolean
	 */
	public static function updateToken($idToken, array $data) {
		$idToken	= (int) $idToken;

		self::removeTokenFromCache($idToken);

		return TodoyuRecordManager::updateRecord(self::TABLE, $idToken, $data);
	}



	/**
	 * Remove token from cache (only necessary if the token has been loaded from database
	 * and updated after in the same request and needs to be loaded again)
	 *
	 * @param	integer		$idToken
	 */
	public static function removeTokenFromCache($idToken) {
		$idToken	= (int) $idToken;

		TodoyuRecordManager::removeRecordCache('TodoyuToken', $idToken);
		TodoyuRecordManager::removeRecordQueryCache(self::TABLE, $idToken);
	}



	/**
	 * Get hash of token that was received with request
	 *
	 * @return	string
	 */
	public static function geTokenHashValueFromRequest() {
		return TodoyuRequest::getParam('token');
	}



	/**
	 * Check whether token has been received with request
	 *
	 * @return	boolean
	 */
	public static function hasRequestToken() {
		$hash	= self::geTokenHashValueFromRequest();

		return ! empty($hash);
	}



	/**
	 * Get todoyu sharing URL for accessing public token of given specs
	 *
	 * @param	integer		$extID
	 * @param	integer		$idTokenType
	 * @param	boolean		$asDownload
	 * @param	integer		$idPerson
	 * @return	string|Boolean
	 */
	public static function getTokenURL($extID, $idTokenType, $asDownload = false, $idPerson = 0) {
		$extID			= (int) $extID;
		$idTokenType	= (int) $idTokenType;

		$token	= TodoyuTokenManager::getTokenByPerson($extID, $idTokenType, $idPerson);

		if( $token ) {
			$urlParams = array(
				'token'	=> $token->getHash()
			);
			if( $asDownload ) {
				$urlParams['download']	= 1;
			}

			return TodoyuString::buildUrl($urlParams, '', true);
		}

		return false;
	}



	/**
	 * Check whether a token of the given specs is stored already
	 *
	 * @param	integer		$extID
	 * @param	integer		$idTokenType
	 * @param	integer		$idPerson
	 * @return	boolean
	 */
	public static function isTokenStored($extID, $idTokenType, $idPerson = 0) {
		$extID			= (int) $extID;
		$idTokenType	= (int) $idTokenType;
		$idToken		= self::getTokenIdByPerson($extID, $idTokenType, $idPerson);

		return $idToken !== 0;
	}

}

?>