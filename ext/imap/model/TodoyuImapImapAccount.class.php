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
 * IMAP account object
 *
 * @package		Todoyu
 * @subpackage	Imap
 */
class TodoyuImapImapAccount extends TodoyuBaseObject {

	/**
	 * Constructor of the class
	 * 
	 * @param	integer	$idAccount
	 */
	public function __construct($idAccount) {
		parent::__construct($idAccount, 'ext_imap_account');
	}



	/**
	 * Check whether account is active
	 *
	 * @return	boolean
	 */
	public function isActive() {
		return $this->isFlagSet('is_active');
	}



	/**
	 * Get account host name
	 *
	 * @return	string
	 */
	public function getHost() {
		return $this->data['host'];
	}



	/**
	 * Get account username
	 *
	 * @return	string
	 */
	public function getUsername() {
		return $this->data['username'];
	}



	/**
	 * Get account password
	 *
	 * @return	string
	 */
	public function getPassword() {
		return TodoyuCrypto::decrypt($this->data['password']);
	}



	/**
	 * Decrypt the account password
	 */
	public function decryptPassword() {
		$this->data['password']	= $this->getPassword();
	}



	/**
	 * Get account folder name
	 *
	 * @return	string
	 */
	public function getFolder() {
		return $this->data['folder'];
	}



	/**
	 * Get folder delimiter
	 *
	 * @return	string
	 */
	public function getDelimiter() {
		return $this->data['delimiter'];
	}



	/**
	 * Get account port
	 *
	 * @return	integer
	 */
	public function getPort() {
		return $this->getInt('port');
	}



	/**
	 * Check whether TLS should be used
	 *
	 * @return	boolean
	 */
	public function isUsedTLS() {
		return $this->isFlagSet('use_tls');
	}



	/**
	 * Check whether SSL should be used
	 *
	 * @return	boolean
	 */
	public function isUsedSSL() {
		return $this->isFlagSet('use_ssl');
	}



	/**
	 * Check if certificate is required or not
	 *
	 * @return	boolean
	 */
	public function isUsedCertNoValidate() {
		return $this->isFlagSet('cert_novalidate');
	}



	/**
	 * Get account label (host:username)
	 *
	 * @param	boolean		$withFlags
	 * @return	string
	 */
	public function getLabel($withFlags = true) {
		$label	= $this->getUsername() . ', ' . $this->getHost();
		$flags	= array();

			// Collect flags
		if( $withFlags ) {
			if( $this->isActive() ) {
				$flags[] = 'active';

				$flags	= TodoyuHookManager::callHookDataModifier('imap', 'account.flags', $flags, array($this->getID(), $this));
			}

			if( !empty($flags) ) {
				$label .= ' (' . implode(', ', $flags) . ')';
			}
		}

		return $label;
	}



	/**
	 * Get config for mailbox
	 *
	 * @return	Array
	 */
	public function getMailboxConfig() {
		return  array(
			'host'		=> $this->getHost(),
			'username'	=> $this->getUsername(),
			'password'	=> $this->getPassword(),
			'port'		=> $this->getPort(),
			'starttls'	=> $this->isUsedTLS(),
			'ssl'		=> $this->isUsedSSL(),
			'folder'	=> $this->getFolder(),
			'delimiter'	=> $this->getDelimiter(),
			'novalidate'	=> $this->isUsedCertNoValidate()
		);
	}



	/**
	 * Get account mailbox
	 *
	 * @param	array		$options
	 * @return	TodoyuImapMailbox|Boolean
	 */
	public function getMailbox(array $options = array()) {
		return TodoyuImapMailboxManager::getMailbox($this, $options);
	}



	/**
	 * Load account foreign data (authorized account users)
	 *
	 */
	protected function loadForeignData() {

	}



	/**
	 * Get template data.
	 * Decrypt password
	 *
	 * @param	boolean		$loadForeignData
	 * @return	Array
	 */
	public function getTemplateData($loadForeignData = false) {
		if( $loadForeignData ) {
			self::loadForeignData();
		}

		$data	= parent::getTemplateData();

		$data['password'] = $this->getPassword();

		return $data;
	}

}

?>