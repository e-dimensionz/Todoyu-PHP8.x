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
 * (Email) message
 * Imported from IMAP server
 *
 * @package			Todoyu
 * @subpackage		Imap
 */
class TodoyuImapMessage extends TodoyuBaseObject {

	/**
	 * Initialize
	 *
	 * @param	integer		$idMessage
	 */
	public function __construct($idMessage) {
		parent::__construct($idMessage, 'ext_imap_message');
	}



	/**
	 * Get date sent
	 *
	 * @return	integer
	 */
	public function getDateSent() {
		return $this->getInt('date_sent');
	}



	/**
	 * Get subject
	 *
	 * @return	string
	 */
	public function getSubject() {
		return $this->get('subject');
	}



	/**
	 * Get message id
	 * Not the record ID, it's the global message id
	 *
	 * @return	string
	 */
	public function getMessageID() {
		return $this->get('message_id');
	}



	/**
	 * Get message size
	 *
	 * @return	integer
	 */
	public function getSize() {
		return $this->getInt('size');
	}



	/**
	 * Get amount of attachments
	 *
	 * @return	integer
	 */
	public function getAmountAttachments() {
		return $this->getInt('amount_attachments');
	}



	/**
	 * Check whether message has attachments
	 *
	 * @return	boolean
	 */
	public function hasAttachments() {
		return $this->getAmountAttachments() > 0;
	}



	/**
	 * Get attachment IDs
	 *
	 * @return	integer[]
	 */
	public function getAttachmentIDs() {
		$field	= 'id';
		$table	= 'ext_imap_attachment';
		$where	= '		id_message	= ' . $this->getID()
				. ' AND	deleted		= 0';

		return Todoyu::db()->getColumn($field, $table, $where);
	}



	/**
	 * Get attachments
	 *
	 * @return	TodoyuImapAttachment[]
	 */
	public function getAttachments() {
		$attachmentIDs	= $this->getAttachmentIDs();

		return TodoyuRecordManager::getRecordList('TodoyuImapAttachment', $attachmentIDs);
	}



	/**
	 * Get message html part
	 *
	 * @param	boolean		$fixInlineImages		Replace inline image paths with todoyu URIs
	 * @return	string
	 */
	public function getMessageHtml($fixInlineImages = false) {
		$messageHtml	=  $this->get('message_html');

		if( $fixInlineImages ) {
			$messageHtml	= TodoyuImapMessageManager::replaceInlineImagePaths($messageHtml);
		}

		return $messageHtml;
	}



	/**
	 * Get message plain text part
	 *
	 * @return	string
	 */
	public function getMessagePlain() {
		return $this->get('message_plain');
	}



	/**
	 * Get safe html message content
	 *
	 * @param	boolean		$fixInlineImages		Fix the paths to the extracted inline images
	 * @return	string
	 */
	public function getMessageHtmlSafe($fixInlineImages = false) {
		return TodoyuImapHtml::getSafeHtml($this->getMessageHtml($fixInlineImages));
	}



	/**
	 * Get message content safe and simplified
	 *
	 * @return	string
	 */
	public function getMessageSafeAndSimple() {
		if( $this->hasHtml() ) {
			return $this->getMessageHtmlSafeAndSimple();
		} else {
			return $this->getTextCleanedAsSimpleHtml();
		}
	}



	/**
	 * Get message cleaned and with simplified html
	 * Ready to insert as comment text
	 *
	 * @return	string
	 */
	public function getMessageHtmlSafeAndSimple() {
		return TodoyuImapHtml::getSaveAndSimpleHtml($this->getMessageHtml());
	}




	/**
	 * Get cleaned html version of text content
	 *
	 * @return	string
	 */
	private function getTextCleanedAsSimpleHtml() {
		return nl2br($this->getMessagePlain());
	}

	

	/**
	 * @return	boolean
	 */
	public function isHtmlAndPlainText() {
		return $this->hasHtml() && $this->hasText();
	}



	/**
	 * Check whether message has HTML content
	 *
	 * @return	boolean
	 */
	public function hasHtml() {
		return trim($this->getMessageHtml()) !== '';
	}



	/**
	 * Check whether message has plain text content
	 *
	 * @return	boolean
	 */
	public function hasText() {
		return trim($this->getMessagePlain()) !== '';
	}



	/**
	 * Check whether message is text only
	 *
	 * @return	boolean
	 */
	public function isTextOnly() {
		return $this->hasText() && !$this->hasHtml();
	}



	/**
	 * Check whether message is HTML only
	 *
	 * @return	boolean
	 */
	public function isHtmlOnly() {
		return !$this->hasText() && $this->hasHtml();
	}



	/**
	 * Get available format, depending on the requested format
	 *
	 * @param	string		$requestedFormat
	 * @return	string
	 */
	public function getAvailableFormat($requestedFormat) {
		$requestedFormat	= trim(strtolower($requestedFormat));

		switch( $requestedFormat ) {
			case 'text':
				return $this->hasText() ? 'text' : 'html';
			case 'html':
			default:
				return $this->hasHtml() ? 'html' : 'text';
		}
	}



	/**
	 * Assert a format is set or a fallback is used
	 *
	 * @param	string		$format
	 * @return	string
	 */
	public function assertFormat($format) {
		$format	= strtolower(trim($format));

		if( empty($format) ) {
			$format = $this->getAvailableFormat('html');
		}

		return $format;
	}




	/**
	 * Get from email address ID
	 *
	 * @return	integer
	 */
	public function getAddressFromID() {
		return $this->getInt('id_address_from');
	}



	/**
	 * Get "from" email address
	 *
	 * @return	TodoyuImapAddress
	 */
	public function getAddressFrom() {
		return TodoyuImapAddressManager::getAddress($this->getAddressFromID());
	}



	/**
	 * Get addresses objects for TO addresses
	 *
	 * @return	TodoyuImapAddress[]
	 */
	public function getAddressesTo() {
		return $this->getAddressesType(IMAP_ADDRESS_TYPE_TO);
	}



	/**
	 * Get addresses IDs for TO addresses
	 *
	 * @return	integer[]
	 */
	public function getAddressesToIDs() {
		return $this->getAddressesTypeIDs(IMAP_ADDRESS_TYPE_TO);
	}



	/**
	 * Get addresses data for TO addresses
	 *
	 * @return	Array[]
	 */
	public function getAddressesToData() {
		return $this->getAddressesTypeData(IMAP_ADDRESS_TYPE_TO);
	}



	/**
	 * Get addresses objects for CC addresses
	 *
	 * @return	TodoyuImapAddress[]
	 */
	public function getAddressesCc() {
		return TodoyuImapAddressManager::getAddressesByType($this->getID(), IMAP_ADDRESS_TYPE_CC);
	}



	/**
	 * Get addresses IDs for CC addresses
	 *
	 * @return Integer[]
	 */
	public function getAddressesCcIDs() {
		return $this->getAddressesTypeIDs(IMAP_ADDRESS_TYPE_CC);
	}



	/**
	 * Get addresses data for CC addresses
	 *
	 * @return	Array[]
	 */
	public function getAddressesCcData() {
		return $this->getAddressesTypeData(IMAP_ADDRESS_TYPE_CC);
	}



	/**
	 * Get addresses object for replyto addresses
	 *
	 * @return	TodoyuImapAddress[]
	 */
	public function getAddressesReplyTo() {
		return $this->getAddressesType(IMAP_ADDRESS_TYPE_REPLYTO);
	}



	/**
	 * Get addresses IDs for replyto addresses
	 *
	 * @return Integer[]
	 */
	public function getAddressesReplyToIDs() {
		return $this->getAddressesTypeIDs(IMAP_ADDRESS_TYPE_REPLYTO);
	}



	/**
	 * Get addresses data for replyto addresses
	 *
	 * @return	Array[]
	 */
	public function getAddressesReplyToData() {
		return $this->getAddressesTypeData(IMAP_ADDRESS_TYPE_REPLYTO);
	}



	/**
	 * Get addresses IDs for type
	 *
	 * @param	integer		$type
	 * @return	integer[]
	 */
	protected function getAddressesTypeIDs($type) {
		return TodoyuImapAddressManager::getAddressIDsByType($this->getID(), $type);
	}



	/**
	 * Get addresses data for type
	 *
	 * @param	integer		$type
	 * @return	Array[]
	 */
	protected function getAddressesTypeData($type) {
		$addresses	= $this->getAddressesType($type);
		$data		= array();

		foreach($addresses as $address) {
			$data[$address->getID()] = $address->getTemplateData(true);
		}

		return $data;
	}



	/**
	 * Get addresses objects for type
	 *
	 * @param	integer		$type
	 * @return	TodoyuImapAddress[]
	 */
	protected function getAddressesType($type) {
		return TodoyuImapAddressManager::getAddressesByType($this->getID(), $type);
	}



	/**
	 * Get account ID
	 *
	 * @return	integer
	 */
	public function getAccountID() {
		return $this->getInt('id_account');
	}


	/**
	 * Get account
	 *
	 * @return	TodoyuImapImapAccount
	 */
	public function getAccount() {
		return TodoyuImapImapAccountManager::getAccount($this->getAccountID());
	}



	/**
	 * Get mailbox
	 *
	 * @param	array		$options
	 * @return	TodoyuImapMailbox|Boolean
	 */
	public function getMailbox(array $options = array()) {
		return $this->getAccount()->getMailbox($options);
	}



	/**
	 * Get clean message body in HTML.
	 * Taken from message text HTML, fallback: message plain text.
	 *
	 * @return	string
	 */
	public function getCleanMessageHtml() {
		$message = $this->getMessageHtml();
		if( empty($message) ) {
			$message	= '<pre>' . $this->getMessagePlain() . '</pre>';
		}

		return TodoyuHtmlFilter::clean($message);
	}



	/**
	 * Get content for task description from message
	 * HTML or plain text
	 *
	 * @return	string
	 */
	protected function getMailContent() {
		$messageHtml	= $this->getMessageHtml();

		if( $messageHtml !== '' ) {
			return $messageHtml;
		} else {
			return $this->getMessagePlain();
		}
	}



	/**
	 * Get raw message key
	 *
	 * @return	string
	 */
	public function getRawMessageKey() {
		return $this->get('raw_message_key');
	}



	/**
	 * Get raw message
	 *
	 * @return TodoyuImapRawMessage
	 */
	public function getRawMessage() {
		return TodoyuImapRawMessageManager::getRawMessage($this->getRawMessageKey());
	}



	/**
	 * Send message as eml download
	 *
	 */
	public function sendAsEmlDownload() {
		$filename	= TodoyuFileManager::makeCleanFilename($this->getSubject()) . '.eml';

		$this->getRawMessage()->sendAsEmlDownload($filename);
	}



	/**
	 * Get link for eml file download
	 *
	 * @return	string
	 */
	public function getEmlDownloadLink() {
		return TodoyuString::buildUrl(array(
			'ext'		=> 'imap',
			'controller'=> 'message',
			'action'	=> 'eml',
			'message'	=> $this->getID()
		));
	}



	/**
	 * Get message index on the server
	 *
	 * @return	integer
	 */
	public function getIndexOnServer() {
		return $this->getAccount()->getMailbox()->getMessageIndex($this->getID());
	}



	/**
	 * Check whether the html content contains external references
	 * Simple check: external references are included with a source attribute with a absolute url
	 *
	 * @return	boolean
	 */
	public function hasExternalsInHtmlContent() {
		$htmlContent	= $this->getMessageHtmlSafe();

		return stripos($htmlContent, 'src="http') !== 0;
	}



	/**
	 * Move message to trash on the server
	 *
	 * @return	boolean
	 */
	public function trashOnServer() {
		return $this->getAccount()->getMailbox()->trashMessage($this->getID(), true);
	}



	/**
	 * Move message back to inbox on the server
	 *
	 * @return	boolean
	 */
	public function restoreToInboxOnServer() {
		return $this->getAccount()->getMailbox()->restoreMessage($this->getID());
	}



	/**
	 * Flag message as seen on the server
	 *
	 * @return	boolean
	 */
	public function flagAsSeenOnServer() {
		return $this->getAccount()->getMailbox()->flagMessageAsSeen($this->getID());
	}



	/**
	 * Flag message as unseen on the server
	 *
	 * @return	boolean
	 */
	public function flagAsUnseenOnServer() {
		return $this->getAccount()->getMailbox()->flagMessageAsUnseen($this->getID());
	}



	/**
	 * Load foreign data
	 */
	protected function loadForeignData() {
		if( !$this->has('address_from') ) {
			$this->set('address_from', 		$this->getAddressFrom()->getTemplateData());
			$this->set('addresses_to',		$this->getAddressesToData());
			$this->set('addresses_cc', 		$this->getAddressesCcData());
			$this->set('addresses_replyto', $this->getAddressesReplyToData());
		}
	}



	/**
	 * Get template data
	 *
	 * @param	boolean		$loadForeignData
	 * @return	Array
	 */
	public function getTemplateData($loadForeignData = false) {
		if( $loadForeignData ) {
			self::loadForeignData(); // self => make sure to load own foreign data
		}

		$this->set('hasHtmlExternals', $this->hasExternalsInHtmlContent());

		return parent::getTemplateData();
	}

}

?>