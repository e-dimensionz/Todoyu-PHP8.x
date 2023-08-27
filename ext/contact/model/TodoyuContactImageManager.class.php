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
 * Handler for contact (person / company) images
 *
 * @package		Todoyu
 * @subpackage	Contact
 */
class TodoyuContactImageManager {

	/**
	 * Filename of the uploaded image in the files - folder
	 *
	 * @var	String
	 */
	protected static $destFileName = 'contactimage.png';



	/**
	 * @param	integer		$idContact
	 * @param	string		$contactType
	 * @return	boolean
	 */
	public static function hasContactImage($idContact, $contactType){
		return self::hasImage($idContact, $contactType, 'contactimage');
	}



	/**
	 * @param	integer		$idContact
	 * @param	string		$contactType
	 * @return	boolean
	 */
	public static function hasAvatar($idContact, $contactType) {
		return self::hasImage($idContact, $contactType, 'avatar');
	}



	/**
	 * Check whether the contact of given type and ID has an image (real file, not the displayed fallback dummy) assigned
	 *
	 * @param	string		$idContact		ID of TodoyuContactPerson / TodoyuContactCompany
	 * @param	string		$contactType	'person' / 'company'
	 * @param
	 * @return	boolean
	 */
	public static function hasImage($idContact, $contactType, $imageType) {
		$pathContactImage	= self::getPathContactImage($idContact, $contactType, $imageType);

		return TodoyuFileManager::isFile($pathContactImage);
	}



	/**
	 * Returns the preview image of a person
	 *
	 *
	 * @param	TodoyuFormElement_Comment	$formElement
	 * @param	string						$typeKey		'person' / 'company'
	 * @return	string
	 */
	public static function renderImageForm(TodoyuFormElement_Comment $formElement, $typeKey) {
		$idRecord	= intval($formElement->getForm()->getHiddenField('id'));
		$idImage	= $formElement->getForm()->getHiddenField('image_id');

		if( !$idImage ) {
			$idImage	= $idRecord;
		}

		$dummy	= $idRecord === 0 || !self::hasImage($idRecord, $typeKey, 'contactimage');

		return self::getContactImage($idImage, $typeKey, $dummy);
	}



	/**
	 * Returns the image tag of the given contact type (person / company)
	 *
	 * @param	integer		$idImage
	 * @param	string		$typeKey		'person' / 'company'
	 * @param	boolean		$isDummy
	 * @return	string
	 */
	public static function getContactImage($idImage, $typeKey, $isDummy = true) {
		return self::getImage($typeKey, 'contactimage', $idImage, $isDummy);
	}



	/**
	 * @static
	 * @param $idImage
	 * @param $typeKey
	 * @param bool $isDummy
	 * @return string
	 */
	public static function getAvatarImage($idImage, $typeKey, $isDummy = true) {
		return self::getImage($typeKey, 'avatar', $idImage, $isDummy);
	}



	/**
	 * Get image tag with dynamic image url
	 *
	 * @param	string			$typeKey
	 * @param	string			$imageType
	 * @param	string/Integer	$idImage
	 * @param	boolean			$isDummy
	 * @return	string
	 */
	protected static function getImage($typeKey, $imageType, $idImage, $isDummy) {
		$params = array(
			'ext'		=> 'contact',
			'controller'=> $typeKey,
			'action'	=> 'render' . $imageType,
			'idImage'	=> $idImage,
			'dummy'		=> $isDummy ? 1:0,
			'hash'		=> NOW
		);

		$imgSrc = TodoyuString::buildUrl($params);

		return TodoyuString::getImgTag($imgSrc, 0, 0);
	}



	/**
	 * Renders the Image. Needed because the files folder is .htaccess protected.
	 * If no user picture is found, one of 7 random images is taken
	 *
	 * @param	integer		$idImage	ID of person or company
	 * @param	string		$typeKey	'person' / 'company'
	 */
	public static function renderContactImage($idImage, $typeKey) {
			// Image ID === 0 => get random dummy image
		$idImage	=  self::hasContactImage($idImage, $typeKey) ? $idImage : 0;
		$filePath	=  self::getPathContactImage($idImage, $typeKey, 'contactimage');

		self::sendImageToBrowser($filePath, $idImage);
	}



	/**
	 * Render avatar image for person
	 *
	 * @param	string/Integer	$idImage
	 * @param	string			$typeKey
	 */
	public static function renderAvatarImage($idImage, $typeKey) {
		$idImage	=  self::hasAvatar($idImage, $typeKey) ? $idImage : 0;
		$filePath	=  self::getPathContactImage($idImage, $typeKey, 'avatar');

		self::sendImageToBrowser($filePath);
	}



	/**
	 * Send image to browser with
	 *
	 * @param	string		$filePath
	 * @param	integer		[$idImage]		0 = placeholder / > 0 = uploaded image
	 * @return	string		Binary data
	 */
	protected static function sendImageToBrowser($filePath, $idImage = 0) {
		TodoyuHeader::sendContentType('image/png');
		TodoyuHeader::sendHeader('Content-Transfer-Encoding', 'binary');
		TodoyuHeader::sendHeader('Expires', date('r', NOW + TodoyuTime::SECONDS_WEEK*4));
		TodoyuHeader::sendHeader('Pragma', 'cache');
		TodoyuHeader::sendHeader('Cache-Control', 'max-age=50000');
		TodoyuHeader::sendHeader('User-Cache-Control', 'max-age=50000');

		TodoyuHeader::sendTodoyuHeader('imageid', $idImage);

		echo file_get_contents($filePath);
		exit();
	}



	/**
	 * Store a file
	 * - Add file to storage folder
	 * - Update the contact record
	 *
	 * @param	string		$path
	 * @param	string		$name
	 * @param	string		$mime
	 * @param	integer		$idRecord		ID of TodoyuContactPerson / TodoyuContactCompany
	 * @param	string		$typeKey		'person' / 'company'
	 * @return	integer
	 */
	public static function storeImage($path, $name, $mime, $idRecord, $typeKey) {
		$idRecord	= intval($idRecord);

		if( $idRecord == 0 ) {
			$idRecord	= md5(NOW);
			$new		= true;
		} else {
			$new	= false;
		}

		self::storeContactImage($idRecord, $path, $typeKey);
		self::storeAvatarImage($idRecord, $path, $typeKey);

		return $new ? $idRecord : 0;
	}



	/**
	 *
	 *
	 * @param $idRecord
	 * @param $path
	 * @param $typeKey
	 */
	protected static function storeContactImage($idRecord, $path, $typeKey) {
		$pathResizedImage	= self::getPathStorageDir($typeKey, 'contactimage') . '/' . $idRecord . '/' . self::$destFileName;
		$dimension			= self::getDimension('contactimage');

		TodoyuImageManager::saveResizedImage($path, $pathResizedImage, $dimension['x'], $dimension['y'], null, true);
	}



	/**
	 *
	 *
	 * @param	integer		$idRecord
	 * @param	string		$path
	 * @param	string		$typeKey
	 */
	protected static function storeAvatarImage($idRecord, $path, $typeKey) {
		$pathResizedImage	= self::getPathStorageDir($typeKey, 'avatar') . '/' . $idRecord . '/' . self::$destFileName;
		$dimension			= self::getDimension('avatar');

		TodoyuImageManager::saveResizedImage($path, $pathResizedImage, $dimension['x'], $dimension['y'], null, true);
	}



	/**
	 * Get path to profile image of contact record of given type + ID
	 *
	 * @param	integer		$idContact			ID of TodoyuContactPerson / TodoyuContactCompany
	 * @param	string		$contactType		'person' / 'company'
	 * @param	string		$imageType
	 * @return	string
	 */
	protected static function getPathContactImage($idContact, $contactType, $imageType) {
		if( $idContact === 0 ) {
				// Get random dummy image
			$path	= PATH . '/ext/contact/asset/img/persondefault/user0' . rand(1, 6) . '.png';
		} else {
			$path	= self::getPathStorageDir($contactType, $imageType)  . '/' . $idContact . '/' . self::$destFileName;
		}

		return $path;
	}



	/**
	 * Rename storage folder.
	 *
	 * @param	string		$typeKey 			'person' / 'company'
	 * @param	string		$temporaryImageKey
	 * @param	integer		$idRecord
	 */
	public static function renameStorageFolder($typeKey, $temporaryImageKey, $idRecord) {
		$storagePathContactImage	= self::getPathStorageDir($typeKey, 'contactimage');
		$storagePathAvatar			= self::getPathStorageDir($typeKey, 'avatar');

		self::renameFile($storagePathContactImage, $temporaryImageKey, $idRecord);
		self::renameFile($storagePathAvatar, $temporaryImageKey, $idRecord);
	}



	/**
	 * @static
	 * @param	string			$storagePath
	 * @param	string/Integer	$temporaryImageKey
	 * @param	integer			$idRecord
	 */
	protected static function renameFile($storagePath, $temporaryImageKey, $idRecord) {
		if (is_dir($storagePath)) {
			TodoyuFileManager::rename($storagePath . '/' . $temporaryImageKey, $storagePath . '/' . $idRecord);
		}
	}



	/**
	 * Removes the Image from the file folder
	 *
	 * @param	integer		$idImage
	 * @param	string		$typeKey		'person' / 'company'
	 */
	public static function removeImage($idImage, $typeKey) {
		$storageDir	= self::getPathStorageDir($typeKey, 'contactimage');
		self::removeFile($storageDir, $idImage);
		$storageDir	= self::getPathStorageDir($typeKey, 'avatar');
		self::removeFile($storageDir, $idImage);
	}



	/**
	 * @static
	 * @param	string			$storageDir
	 * @param	string/Integer	$idImage
	 */
	protected static function removeFile($storageDir, $idImage) {
		$dir = $storageDir . '/' . $idImage;
		$file = $dir . '/' . self::$destFileName;

		if (is_file($file)) {
			unlink($file);
			rmdir($dir);
		}
	}



	/**
	 * Check whether the type of the uploaded file is in the allowed types
	 *
	 * @param	string	$typeKey	'person' / 'company'
	 * @return	boolean
	 */
	public static function checkFileType($typeKey) {
		$allowedTypes = self::getAllowedTypes('contactimage');

		if( !in_array($typeKey, $allowedTypes) ) {
			return false;
		}

		return true;
	}



	/**
	 * Get allowed image mime types
	 *
	 * @param	string		$imageType
	 * @return	Array
	 */
	protected static function getAllowedTypes($imageType) {
		return TodoyuArray::assure(Todoyu::$CONFIG['EXT']['contact'][$imageType]['allowedTypes']);
	}



	/**
	 * Returns the dimension of a picture (set in contact/config/init.php)
	 *
	 * @param	string		$imageType
	 * @return	Array
	 */
	protected static function getDimension($imageType) {
		return TodoyuArray::assure(Todoyu::$CONFIG['EXT']['contact'][$imageType]['dimension']);
	}



	/**
	 * Get path to storage directory
	 *
	 * @param	string		$contactType		'person' / 'company'
	 * @param	string		$imageType
	 * @return	string
	 */
	protected static function getPathStorageDir($contactType, $imageType) {
		return TodoyuFileManager::pathAbsolute(Todoyu::$CONFIG['EXT']['contact'][$imageType]['path' . $contactType]);
	}



	/**
	 * Gets the web-path to the image
	 *
	 * @param	string		$contactType		'person' / 'company'
	 * @param	string		$imageType
	 * @return	string
	 */
	protected static function getPathWebDir($contactType, $imageType) {
		return TodoyuFileManager::pathWeb(Todoyu::$CONFIG['EXT']['contact'][$imageType]['path' . $contactType]);
	}
}

?>