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
 * todoyu wrapper class for PHP IMAP functions (retrieval of email messages from mail server via IMAP protocol)
 *
 * @package			Todoyu
 * @subpackage		Imap
 * @see				http://www.php.net/manual/en/book.imap.php
 */
class TodoyuImapMailbox {

	/**
	 * @var	Array		Default config
	 */
	protected $config = array(
		'host'			=> '',
		'username'		=> '',
		'password'		=> '',
		'port'			=> 0,
		'starttls'		=> false,
		'ssl'			=> false,
		'folder'		=> 'INBOX',
		'delimiter'		=> '.',
		'novalidate'	=> false
	);

	/**
	 * @var	Array	$options		Special options
	 */
	protected $options = array();

	/**
	 * @var	TodoyuImapImapAccount	Account
	 */
	protected $account;

	/**
	 * @var	Resource	Connection stream
	 */
	protected $stream;

	/**
	 * @var	String		Config string of active connection
	 */
	protected $connectionString;

	/**
	 * @var	Array		Cached index to message id map
	 */
	protected $cachedIndexToIdMap;



	/**
	 * Initialize with account and options
	 *
	 * @param	TodoyuImapImapAccount	$account
	 * @param	array				$options
	 */
	public function __construct(TodoyuImapImapAccount $account, array $options = array()) {
			// Set account
		$this->account = $account;
			// Initialize config for connection
		$this->initConfig($options);

		if( !$this->isAutoConnectDisabled() ) {
			if( !$this->connect() ) {
				TodoyuLogger::logError('Error while connecting to IMAP mailbox: "' . $this->getMailboxName() . '" - <' . imap_last_error() . '>');
			}
		}
	}



	/**
	 * Close connection
	 *
	 */
	public function __destruct() {
		if( $this->isConnected() ) {
			imap_close($this->stream, CL_EXPUNGE); // Clean up
		}
	}



	/**
	 * Init config from account data
	 *
	 * @param	array		$options
	 */
	protected function initConfig(array $options) {
		$mailboxConfig	= $this->getAccount()->getMailboxConfig();

			// Override folder
		if( $options['folder'] ) {
			$mailboxConfig['folder'] = $options['folder'];
		}

		$mailboxConfig	= TodoyuHookManager::callHookDataModifier('imap', 'mailbox.config', $mailboxConfig, array($options));
		$options		= TodoyuHookManager::callHookDataModifier('imap', 'mailbox.options', $options, array($mailboxConfig));

		$this->config	= TodoyuArray::merge($this->config, $mailboxConfig);
		$this->options	= $options;
	}



	/**
	 * Check whether auto-connect is disabled
	 *
	 * @return	boolean
	 */
	protected function isAutoConnectDisabled() {
		return $this->options['autoconnect'] === false;
	}



	/**
	 * Connect to the server
	 *
	 * @throws	TodoyuImapConnectionException
	 * @return	boolean
	 */
	protected function connect() {
		$mailboxString	= $this->getMailboxString();
//		TodoyuDebug::printInFirebug($mailboxString, '$mailboxString');

		TodoyuErrorHandler::setActive(false);
		$this->stream		= @imap_open($mailboxString, $this->getUsername(), $this->getPassword());
		TodoyuErrorHandler::setActive(true);

		if( !$this->isConnected() ) {
			throw new TodoyuImapConnectionException();
		}

		$this->connectionString = $mailboxString;

		return true;
	}



	/**
	 * Check whether connection was successful and created a stream resource
	 *
	 * @return	boolean
	 */
	public function isConnected() {
		return is_resource($this->stream);
	}



	/**
	 * Get account
	 *
	 * @return	TodoyuImapImapAccount
	 */
	public function getAccount() {
		return $this->account;
	}



	/**
	 * Get connection stream
	 *
	 * @return	Resource
	 */
	public function getStream() {
		return $this->stream;
	}



	/**
	 * Get account ID
	 *
	 * @return	integer
	 */
	public function getAccountID() {
		return $this->getAccount()->getID();
	}



	/**
	 * Get new mailbox for a sub folder
	 *
	 * @param	string		$subFolder			Folder name relative to current folder
	 * @return	TodoyuImapMailbox
	 */
	public function getSubFolderMailbox($subFolder) {
		$options = array(
			'folder'	=> $this->config['folder'] . '.' . $subFolder
		);

		return TodoyuImapMailboxManager::getMailbox($this->getAccount(), $options);
	}



	/**
	 * Get mailbox name by config.
	 * Contains server and postbox path configuration and server parameters: IP address and port
	 *
	 * @param	string		$subFolder			Name of the mailbox/sub folder
	 * @return	string
	 */
	protected function getMailboxString($subFolder = '') {
		$mailboxName	= $this->getHost();
		$folder			= $this->buildFolderPath($subFolder);

		if( $this->hasPort() ) {
			$mailboxName .= ':' . $this->getPort();
		}

		if( $this->isUsedSSL() ) {
			$mailboxName .= '/ssl';
		} elseif( $this->isUsedStartTLS() ) {
			$mailboxName .= '/tls';
		}

		if($this->isUsedNoValidateCert()){
			$mailboxName .= '/novalidate-cert';
		}

		return '{' . $mailboxName . '}' . $folder;
	}



	/**
	 * Get config string of active connection
	 *
	 * @return	string
	 */
	public function getConnectionString() {
		return $this->connectionString;
	}



	/**
	 * Get general infos about the current mailbox.
	 * Info object contains: date of last change, driver, mailbox name, number of messages, number of recent messages, number of unread messages, number of deleted messages, mailbox size
	 *
	 * @return	Array
	 * @see http://www.php.net/manual/en/function.imap-mailboxmsginfo.php
	 */
	public function getMailboxInfos(){
		if( !$this->isConnected() ) {
			TodoyuLogger::logError('Couldn\'t open IMAP stream. Mailbox: "' . $this->getMailboxString() . '"');
			return false;
		}

		return TodoyuArray::assure(imap_mailboxmsginfo($this->stream));
	}



	/**
	 * Get name of current mailbox
	 *
	 * @return	string
	 */
	public function getMailboxName() {
		$mailboxInfos	= $this->getMailboxInfos();

		return $mailboxInfos['Mailbox'];
	}



	/**
	 * Get message header infos of all emails within mailbox, sorted by date descending
	 *
	 * @return	Array[]
	 */
	public function getMessagesMapIndexToId() {
		if( is_null($this->cachedIndexToIdMap) ) {
			$this->cachedIndexToIdMap	= array();
			$messagesHeaders			= $this->getMessagesHeaders();

			foreach($messagesHeaders as $messageHeader) {
				$messageHeader		= trim($messageHeader);
				$headerStringInfos	= TodoyuImapServerMessageManager::getMessageHeaderStringInfo($messageHeader);

					// Parsing failed?
				if( !is_array($headerStringInfos) ) {
					TodoyuLogger::logError('Failed parsing of message header <' . $messageHeader . '>');
					continue;
				}

					// Ignore deleted messages
				if( $headerStringInfos['flags']['D'] ) {
					continue;
				}

					// DEV: unread
	//			if( !$headerStringInfos['flags']['U'] ) {
	//				continue;
	//			}

				$messageNumber	= $headerStringInfos['number'];
				$headerInfo 	= imap_headerinfo($this->stream, $messageNumber);

				$this->cachedIndexToIdMap[intval($headerInfo->Msgno)] = TodoyuImapMessageManager::cleanID($headerInfo->message_id);
			}
		}
		
		return $this->cachedIndexToIdMap;
	}



	/**
	 * Get current index/number of the message on the server
	 * 0 if message is not found in the inbox
	 *
	 * @param	integer		$idMessage
	 * @return	integer
	 */
	public function getMessageIndex($idMessage) {
		$idMessage	= intval($idMessage);
		$message	= TodoyuImapMessageManager::getMessage($idMessage);

		$messageID	= $message->getMessageID();
		$mapIndexId = $this->getMessagesMapIndexToId();
		$mapIdIndex	= array_flip($mapIndexId);

		return intval($mapIdIndex[$messageID]);
	}



	/**
	 * Get list of folders inside mailbox
	 *
	 * @param	string	$pattern		Optionally specifies where in the mailbox hierarchy to start searching
	 * @param	string	$subFolder		Optional sub folder to base folder (base may be INBOX)
	 * @return	Array
	 */
	public function getFolders($pattern = IMAP_FOLDER_SEARCHPATTERN_ALL, $subFolder = '') {
		$mailboxString	= $this->getMailboxString($subFolder);

		return imap_list($this->stream, $mailboxString, $pattern);
	}



	/**
	 * Get email address string from imap's stdClass address object
	 *
	 * @param	string|stdClass	$address				Properties: personal (fullname), mailbox, host
	 * @return	Array
	 */
	public static function getMailAddressData($address) {
			// Is stdClass IMAP address object
		if( is_object($address) ) {
			return array(
				'mailbox'		=> $address->mailbox,
				'host'			=> $address->host,
				'emailaddress'	=> $address->mailbox . '@' . $address->host,
				'name'			=> $address->personal
			);
		}

			// Is string, with fullname and mail?
		if( is_string($address) && strpos($address, '<') !== false ) {
			list($name, $address)	= explode('<', $address);
		} else {
				// Only email
			$name	= '';
		}

		return array(
			'emailaddress'	=> str_replace(array('<', '>'), '', $address),
			'name'			=> trim($name)
		);
	}



	/**
	 * @param	Object		$subStructure
	 * @param	integer		$partNumber
	 * @param	integer		$messageNumber
	 * @return	Array
	 */
	public static function buildAttachmentDataMessagePart($subStructure, $partNumber = 2, $messageNumber = null) {
		$attachmentData	= array(
			'type'	=> $subStructure->subtype,
			'bytes'	=> $subStructure->bytes,
			'name'	=> $subStructure->parameters[0]->value,
			'part'	=> $partNumber
		);

		if( ! is_null($messageNumber) ) {
			$attachmentData['msgno']	= $messageNumber;
		}

		return $attachmentData;
	}



	/**
	 * @return	Array
	 */
	public function getConfig() {
		return $this->config;
	}



	/**
	 * @return	string
	 */
	public function getHost(){
		return $this->config['host'];
	}



	/**
	 * @return	string
	 */
	public function getUsername(){
		return $this->config['username'];
	}



	/**
	 * Get password
	 *
	 * @return	string
	 */
	public function getPassword(){
		return $this->config['password'];
	}



	/**
	 * Get base folder
	 *
	 * @return	string
	 */
	public function getFolder() {
		return trim($this->config['folder']);
	}



	/**
	 * Get folder path delimiter
	 *
	 * @return	string
	 */
	public function getDelimiter() {
		return $this->config['delimiter'];
	}




	/**
	 * Get port
	 *
	 * @return	integer
	 */
	public function getPort(){
		return intval($this->config['port']);
	}



	/**
	 * Check whether a port is defined
	 * @return bool
	 */
	public function hasPort() {
		return $this->getPort() !== 0;
	}



	/**
	 * Check whether TLS encryption is enabled
	 *
	 * @return	boolean
	 */
	public function isUsedStartTLS(){
		return !!$this->config['starttls'];
	}



	/**
	 * Check whether SSL encryption is enabled
	 *
	 * @return	boolean
	 */
	public function isUsedSSL() {
		return !!$this->config['ssl'];
	}



	/**
	 * Check whether cert validateion is disabled or not
	 *
	 * @return	boolean
	 */
	public function isUsedNoValidateCert(){
		return !!$this->config['novalidate'];
	}



	/**
	 * Get headers for all messages in this mailbox, sorted by msgno
	 *
	 * @return	string[]		Array of strings formatted with info. One element per mail message
	 * @see		http://www.php.net/manual/en/function.imap-headers.php
	 */
	private function getMessagesHeaders() {
		return imap_headers($this->stream);
	}



	/**
	 * Get given section of message body of message with given number
	 *
	 * @param	integer		$messageNumber
	 * @param	integer		$section
	 * @param	integer		$options		Optional bitmask (FT_UID / FT_PEEK / FT_INTERNAL)
	 * @return	string
	 * @see		http://www.php.net/manual/en/function.imap-fetchbody.php
	 */
	private function getMessageBodySection($messageNumber, $section, $options = 0) {
		$options	|= FT_PEEK;

		return imap_fetchbody($this->stream, $messageNumber, $section, $options);
	}



	/**
	 * Get message body plain text
	 *
	 * @param	integer		$messageNumber
	 * @param	integer		$part
	 * @return	string
	 */
	public function getMessageBodyPlain($messageNumber, $part = null) {
		$section	= !is_null($part) ? $part . '.1' : '1';

		return $this->getMessageBodySection($messageNumber, $section);
	}



	/**
	 * Get message body HTML text
	 *
	 * @param	integer		$messageNumber
	 * @param	integer		$part
	 * @return	string
	 */
	public function getMessageBodyHtml($messageNumber, $part = null) {
		$section	= !is_null($part) ? $part . '.2' : '2';

		return $this->getMessageBodySection($messageNumber, $section);
	}



	/**
	 * Get total amount of messages in mailbox. Returns false if mailbox infos can't be read.
	 *
	 * @param	string				$state
	 * @return	integer
	 */
	public function getAmountMessages($state = IMAP_MESSAGE_STATE_ALL) {
		$mailboxInfos	= $this->getMailboxInfos();

		return intval($mailboxInfos[$state]);
	}



	/**
	 * Check whether mailbox has messages in the state
	 *
	 * @param	string		$state
	 * @return	boolean
	 */
	public function hasMessages($state = IMAP_MESSAGE_STATE_ALL) {
		return $this->getAmountMessages($state) > 0;
	}



	/**
	 * Check whether the mailbox contains any recent messages
	 *
	 * @return	boolean
	 */
	public function hasRecentMessages() {
		return $this->hasMessages(IMAP_MESSAGE_STATE_RECENT);
	}



	/**
	 * Check whether the mailbox contains any deleted messages
	 *
	 * @return	boolean
	 */
	public function hasDeletedMessages() {
		return $this->hasMessages(IMAP_MESSAGE_STATE_DELETED);
	}



	/**
	 * Check whether the mailbox contains any unread messages
	 *
	 * @return	boolean
	 */
	public function hasUnreadMessages() {
		return $this->hasMessages(IMAP_MESSAGE_STATE_UNREAD);
	}


	/**
	 * Get total amount of messages
	 *
	 * @return	integer
	 */
	public function getAmountMessagesTotal() {
		return $this->getAmountMessages(IMAP_MESSAGE_STATE_ALL);
	}



	/**
	 * Get amount of unread messages
	 *
	 * @return	integer
	 */
	public function getAmountMessagesUnread() {
		return $this->getAmountMessages(IMAP_MESSAGE_STATE_UNREAD);
	}



	/**
	 * Get amount of recent messages
	 *
	 * @return	integer
	 */
	public function getAmountMessagesRecent() {
		return $this->getAmountMessages(IMAP_MESSAGE_STATE_RECENT);
	}



	/**
	 * Get amount of deleted messages
	 *
	 * @return	integer
	 */
	public function getAmountMessagesDeleted() {
		return $this->getAmountMessages(IMAP_MESSAGE_STATE_DELETED);
	}



	/**
	 * Get mailbox size
	 *
	 * @return	integer
	 */
	public function getSize() {
		$mailboxInfos	= $this->getMailboxInfos();

		return intval($mailboxInfos['Size']);
	}



	/**
	 * Get a server message by index/no
	 *
	 * @param	integer		$messageNo
	 * @return	TodoyuImapServerMessage
	 */
	public function getServerMessage($messageNo) {
		return new TodoyuImapServerMessage($this, $messageNo);
	}


	
	/**
	 * Import not imported messages
	 *
	 * Messages 		=> ext_imap_message
	 * Email addresses	=> ext_imap_address
	 * Attachment files	=> ext_imap_attachment
	 */
	public function importNewMessages() {
		$idAccount 			= $this->getAccountID();
		$newMessageIndexes	= $this->getNewMessageIndexes();

		foreach($newMessageIndexes as $messageIndex) {
			$serverMessage		= $this->getServerMessage($messageIndex);

				// Call hook
			TodoyuHookManager::callHook('imap', 'message.import', array($serverMessage));

				// Store FROM email: ext_imap_address + ID in ext_imap_message
			if( $serverMessage->hasSenderAddress() ) {
				$idAddressFrom = TodoyuImapMessageManager::saveAddress($serverMessage->getSenderAddressData());
			} else {
				$idAddressFrom = TodoyuImapMessageManager::saveAddress($serverMessage->getFromAddressData());
			}

				// Save raw message parts
			$rawMessageKey= TodoyuImapRawMessageManager::saveRawMessage($serverMessage->getRawHeader(), $serverMessage->getRawBody());

				// Store message record to database
			$recordData	= array(
				'message_id'			=> $serverMessage->getMessageID(),	// IMAP message UID
				'date_sent'				=> $serverMessage->getDate(),
				'subject'				=> $serverMessage->getSubject(),
				'size'					=> $serverMessage->getSize(),
				'id_account'			=> $idAccount,
				'id_address_from'		=> $idAddressFrom,
				'message_plain'			=> $serverMessage->getContentPlain(),
				'message_html'			=> $serverMessage->getContentHtml(),
				'amount_attachments'	=> $serverMessage->getAmountAttachments(),
				'raw_message_key'		=> $rawMessageKey
			);
			$idMessage	= TodoyuImapMessageManager::addMessage($recordData);

				// Save addresses
			TodoyuImapMessageManager::saveAddressesToAddressBook($idMessage, $serverMessage->getToAddressesData(), IMAP_ADDRESS_TYPE_TO);
			TodoyuImapMessageManager::saveAddressesToAddressBook($idMessage, $serverMessage->getReplyToAddressesData(), IMAP_ADDRESS_TYPE_REPLYTO);
			TodoyuImapMessageManager::saveAddressesToAddressBook($idMessage, $serverMessage->getCcAddressesData(), IMAP_ADDRESS_TYPE_CC);

				// Store attachments
			foreach($serverMessage->getAttachments() as $messageAttachment) {
				$idAttachment = $messageAttachment->saveAsAttachment($idMessage);
				TodoyuLogger::logDebug('Add attachment <' . $idAttachment . '> for message <' . $idMessage . '>');
			}

				// Store inline images
			foreach($serverMessage->getInlineImages() as $inlineImage) {
				$pathImage = $inlineImage->saveInFiles();
				TodoyuLogger::logDebug('Store inline image <' . $pathImage . '> for message <' . $idMessage . '>');
			}

				// Call hook after created
			TodoyuHookManager::callHook('imap', 'message.imported', array($idMessage));
		}

		TodoyuHookManager::callHook('imap', 'messages.imported', array($this));
	}



	/**
	 * Get message indexes of not imported messages
	 *
	 * @return	Array
	 */
	protected function getNewMessageIndexes() {
		$messageNumberIdMap	= $this->getMessagesMapIndexToId();
			// Filter for not yet imported message IDs
		$messageNumberIdMap	= TodoyuImapMessageManager::reduceMessageIDsToNotYetImported($this->getAccountID(), $messageNumberIdMap);

		return array_keys($messageNumberIdMap);
	}



	/**
	 * Delete a message on the server
	 *
	 * @param	integer		$idMessage
	 */
	public function deleteMessageOnServer($idMessage) {
		$idMessage	= intval($idMessage);
		$index		= $this->getMessageIndex($idMessage);

		imap_delete($this->stream, $index);
	}



	/**
	 * Move a message to another account
	 *
	 * @param	integer					$idMessage
	 * @param	TodoyuImapMailbox		$targetMailbox		Mailbox to add the message to
	 * @param	string					$targetFolder		Folder path: A.B.C from INBOX
	 * @param	boolean					$move				Move message (false makes just a copy)
	 * @return	boolean
	 */
	public function moveMessageToMailbox($idMessage, TodoyuImapMailbox $targetMailbox, $targetFolder, $move = true) {
		$idMessage			= intval($idMessage);
		$addedSuccessfully	= $targetMailbox->addMessage($idMessage, $targetFolder);

		if( $addedSuccessfully ) {
			if( $move ) {
				TodoyuLogger::logNotice('Moved message <' . $idMessage . '> to <' . $targetFolder . '>');
				$this->deleteMessageOnServer($idMessage);
			} else {
				TodoyuLogger::logNotice('Copied message <' . $idMessage . '> to <' . $targetFolder . '>');
			}

			return true;
		} else {
			TodoyuLogger::logError('Moving message to account <' . $targetMailbox->getConnectionString() . '> failed for message <' . $idMessage . '> to <' . $targetFolder . '> (' . imap_last_error() . ')');

			return false;
		}
	}



	/**
	 * Copy message to another mailbox
	 *
	 * @param	integer					$idMessage
	 * @param	TodoyuImapMailbox		$targetMailbox		Mailbox to add the message to
	 * @param	string					$targetFolder		Folder path: A.B.C from INBOX
	 * @return	boolean
	 */
	public function copyMessageToMailbox($idMessage, TodoyuImapMailbox $targetMailbox, $targetFolder) {
		return $this->moveMessageToMailbox($idMessage, $targetMailbox, $targetFolder, false);
	}



	/**
	 * Create a new folder (incl. sub folders)
	 *
	 * @param	string		$folder			EX: CustomerName.ProjectName
	 * @return	boolean
	 */
	public function createFolder($folder) {
		$absFolderPath	= $this->getAbsoluteFolderPath($folder);
		$createStatus 	= imap_createmailbox($this->stream, $absFolderPath);

		if( $createStatus ) {
			$subscribeStatus = imap_subscribe($this->stream, $absFolderPath);
		} else {
			TodoyuLogger::logNotice('Creating a new folder on IMAP server failed for <' . $absFolderPath . '> (' . imap_last_error() . ')');
		}

		return $createStatus;
	}



	/**
	 * Check whether folder exists
	 *
	 * @param	string		$folder		Folder path with dotted sub folders A.B.C
	 * @return	boolean
	 */
	public function hasFolder($folder) {
		return $this->getFolderStatus($folder) !== false;
	}



	/**
	 * Get folder status
	 *
	 * @param	string		$folder
	 * @param	integer		$options
	 * @return	stdClass
	 */
	public function getFolderStatus($folder, $options = 0) {
		$absFolderPath	= $this->getAbsoluteFolderPath($folder);

		return imap_status($this->stream, $absFolderPath, $options);
	}



	/**
	 * Append sub folder to base folder
	 *
	 * @param	string		$subFolder
	 * @return	string
	 */
	protected function buildFolderPath($subFolder = '') {
		$folder	= $this->getFolder();

		if( $subFolder ) {
			$subFolder	= TodoyuImapMailboxManager::getAsImapUtf7($subFolder);
			$folder		.= $this->getDelimiter() . $subFolder;
		}

		return $folder;
	}



	/**
	 * Get absolute folder path
	 *
	 * @param	string		$subFolder
	 * @return	string
	 */
	protected function getAbsoluteFolderPath($subFolder) {
		return $this->getMailboxString($subFolder);
	}



	/**
	 * Add/append a message to the mailbox
	 *
	 * @param	integer		$idMessage
	 * @param	string		$folder
	 * @param	integer		$options
	 * @return	boolean
	 */
	public function addMessage($idMessage, $folder, $options = 0) {
		$idMessage		= intval($idMessage);
		$message		= TodoyuImapMessageManager::getMessage($idMessage);
		$messageContent	= $message->getRawMessage()->getMessageContent();
		$mailboxPath	= $this->getAbsoluteFolderPath($folder);

			// Make sure folder/mailbox exists
		$createStatus = $this->createFolder($folder);

		return imap_append($this->stream, $mailboxPath, $messageContent, $options);
	}



	/**
	 * Move a message to the trash
	 *
	 * @param	integer		$idMessage
	 * @param	boolean		$expunge
	 * @return	boolean
	 */
	public function trashMessage($idMessage, $expunge = false) {
		$targetFolder	= $this->buildFolderPath('Trash');

		return $this->moveMessage($idMessage, $targetFolder, $expunge);
	}



	/**
	 * Restore message to inbox
	 *
	 * @param	integer		$idMessage
	 * @return	boolean
	 */
	public function restoreMessage($idMessage) {
		$trashMailbox	= $this->getSubFolderMailbox('Trash');
		$targetFolder	= $this->getFolder();

		return $trashMailbox->moveMessage($idMessage, $targetFolder);
	}



	/**
	 * Move a message to an other folder (mailbox)
	 *
	 * @param	integer		$idMessage
	 * @param	string		$targetFolder
	 * @param	boolean		$expunge
	 * @param	boolean		$copy
	 * @return	boolean
	 */
	public function moveMessage($idMessage, $targetFolder, $expunge = true, $copy = false) {
		$messageIndex	= $this->getMessageIndex($idMessage);
		$options		= CP_MOVE;

			// Message not found?
		if( $messageIndex === 0 ) {
			TodoyuLogger::logError('Moving message <' . $idMessage . '> failed, because message was not found in current mailbox <' . $this->getConnectionString() . '>');
			return false;
		}

			// Disable move
		if( $copy ) {
			$options = 0;
		}

		$moveStatus	= imap_mail_copy($this->stream, $messageIndex, $targetFolder, $options);

		if( !$moveStatus ) {
			TodoyuLogger::logError('Failed to move message to folder <' . $targetFolder . '>, ' . imap_last_error());
		}

		if( $moveStatus && $expunge ) {
			$this->expunge();
		}

		return $moveStatus;
	}



	/**
	 * Copy message to an other folder
	 *
	 * @param	integer		$idMessage
	 * @param	string		$targetFolder
	 * @return	boolean
	 */
	public function copyMessage($idMessage, $targetFolder) {
		return $this->moveMessage($idMessage, $targetFolder, false, true);
	}



	/**
	 * Expunge mailbox
	 * Delete all messages which are flagged deleted
	 *
	 */
	public function expunge() {
		imap_expunge($this->stream);
	}



	/**
	 * Flag message
	 * Flags have to be without backslash
	 *
	 * @see		http://www.php.net/manual/en/function.imap-setflag-full.php
	 * @param	integer		$idMessage
	 * @param	string[]	$flags
	 * @return	boolean
	 */
	public function flagMessage($idMessage, array $flags) {
		$flags		= TodoyuArray::prefixValues($flags, '\\');
		$index		= $this->getMessageIndex($idMessage);
		$flagString	= implode(' ', $flags);

		if( $index === 0 ) {
			TodoyuLogger::logError('Can\'t flag message <' . $idMessage . '> because message was not found on server');
			return false;
		}

		if( empty($flags) ) {
			TodoyuLogger::logNotice('No flags defined for  message <' . $idMessage . '>. Do nothing');
			return false;
		}

		return imap_setflag_full($this->stream, $index, $flagString);
	}



	/**
	 * Clear flags for message
	 *
	 * @param	integer		$idMessage
	 * @param	string[]	$flags
	 * @return	boolean
	 */
	public function clearMessageFlags($idMessage, array $flags) {
		$flags		= TodoyuArray::prefixValues($flags, '\\');
		$index		= $this->getMessageIndex($idMessage);
		$flagString	= implode(' ', $flags);

		if( $index === 0 ) {
			TodoyuLogger::logError('Can\'t clear flag for message  <' . $idMessage . '> because message was not found on server');
			return false;
		}

		if( empty($flags)) {
			TodoyuLogger::logNotice('No flags defined for  message <' . $idMessage . '>. Do nothing');
			return false;
		}

		return imap_clearflag_full($this->stream, $index, $flagString);
	}



	/**
	 * Flag message as seen
	 *
	 * @param	integer		$idMessage
	 * @return	boolean
	 */
	public function flagMessageAsSeen($idMessage) {
		return $this->flagMessage($idMessage, array('Seen'));
	}



	/**
	 * Set message unseen on server
	 *
	 * @param	integer		$idMessage
	 * @return	boolean
	 */
	public function flagMessageAsUnseen($idMessage) {
		return $this->clearMessageFlags($idMessage, array('Seen'));
	}

}

?>