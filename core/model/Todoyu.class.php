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
 * Superglobal object to access important data and objects
 *
 * @package		Todoyu
 * @subpackage	Core
 */
class Todoyu {

	/**
	 * Current system locale
	 *
	 * @var	String
	 */
	private static $locale;

    private static $dates = null;

	/**
	 * Todoyu configuration
	 * All configuration of todoyu and all extensions gets into this static variable
	 *
	 * @var	Array
	 */
	public static $CONFIG	= array();


	/**
	 * Database object instance
	 *
	 * @var	TodoyuDatabase
	 */
	private static $db;


	/**
	 * Templating engine. Currently Smarty
	 *
	 * @var	Smarty
	 */
	private static $template;

	/**
	 * Currently logged in person
	 *
	 * @var	TodoyuContactPerson
	 */
	private static $person;


	/**
	 * Currently used timezone
	 *
	 * @var	String
	 */
	private static $timezone;

	/**
	 * Backup of current environment
	 *
	 * @var	Array
	 */
	private static $environmentBackup;



	/**
	 * Initialize static Todoyu class
	 */
	public static function init() {
			// Set system locale with setlocale
		self::setLocale();
			// Set system timezone
		self::setTimezone();
			// Dummy init of database
		self::db();

			// Add autoloader clear cache hook
		TodoyuHookManager::registerHook('core', 'clearCache', 'TodoyuAutoloader::hookClearCache');
	}



	/**
	 * Set system timezone
	 *
	 * @param	string|Boolean	$forceTimezone		Set new timezone
	 */
	public static function setTimezone($forceTimezone = false) {
		if( !$forceTimezone ) {
			$timezone	= self::getTimezone();
		} else {
			$timezone		= $forceTimezone;
			self::$timezone	= $forceTimezone;
		}

		// initialize the timezone, keep fallback to prevent from fatal error
		try {
			self::initTimezone($timezone);
		} catch(Exception $e) {
			TodoyuLogger::logError('No valid Timezone given from user and system. Initialized with fallback timezone: Europe/Zurich');
			self::initTimezone('Europe/Zurich');
		}
	}



	/**
	 * Initialization Stack for setting the timezone
	 *
	 * @param	string		$timezone
	 */
	protected static function initTimezone($timezone) {
		// Set default timezone
		date_default_timezone_set($timezone);

			// Set DB timezone offset
		$timeZone = new DateTimeZone($timezone);
		$dateTime	= new DateTime('now', $timeZone);
		$timeZoneOffsetHours = $dateTime->format('P');
		self::db()->setTimezone($timeZoneOffsetHours);
	}



	/**
	 * Get active timezone
	 *
	 * @return	string		Timezone string
	 */
	public static function getTimezone() {
		if( is_null(self::$timezone) ) {
			if( self::db()->isConnected() ) {
				$timezone	= self::person()->getTimezone();
			} else {
				$timezone	= false;
			}

			if( !$timezone ) {
				$timezone = self::$CONFIG['SYSTEM']['timezone'];
			}

			self::$timezone = $timezone;
		}

		return self::$timezone;
	}



	/**
	 * Return database object
	 *
	 * @return	TodoyuDatabase
	 */
	public static function db() {
		if( is_null(self::$db) ) {
			self::$db = TodoyuDatabase::getInstance(self::$CONFIG['DB']);
		}

		return self::$db;
	}



	/**
	 * Return template engine
	 *
	 * @return	Smarty
	 */
	public static function tmpl() {
		if( is_null(self::$template) ) {
			$config	= TodoyuArray::assure(self::$CONFIG['TEMPLATE']);

				// Create needed directories
			TodoyuFileManager::makeDirDeep($config['compile']);
			TodoyuFileManager::makeDirDeep($config['cache']);
            
				// Initialize Smarty
			try {
				self::$template = new Smarty();
                // self::$template->setCaching(1);
                self::addSmartyPluginDir('core/model/smarty');
                self::$template->setCacheDir($config['cache'])
                            ->setCompileDir($config['compile']);
			} catch(Exception $e) {
				$msg	= 'Can\'t initialize tempalate engine: ' . $e->getMessage();
				TodoyuLogger::logFatal($msg);
				die($msg);
			}

            TodoyuExtensions::loadAllSmartyPlugins();
		}
        

		return self::$template;
	}



	/**
	 * Add directory for plugins to Smarty
	 *
	 * @param	string		$directory
	 */
	public static function addSmartyPluginDir($directory) {
		$directory	= TodoyuFileManager::pathAbsolute($directory);
        
		self::$template->addPluginsDir($directory);
        if(TodoyuFileManager::isFile($directory.'/functions.php')){
            require_once $directory.'/functions.php';
        }
	}



	/**
	 * Return current person object
	 *
	 * @return	TodoyuContactPerson
	 */
	public static function person() {
		if( is_null(self::$person) ) {
			self::$person = TodoyuAuth::getPerson();
		}

		return self::$person;
	}



	/**
	 * Reset person object if a new person is logged in
	 */
	public static function reset() {
		self::$person = TodoyuAuth::getPerson(true);
		self::$locale = null;
	}



	/**
	 * Get locale: if set get from person profile pref, otherwise from system config
	 *
	 * @return	string
	 */
	public static function getLocale() {
		if( is_null(self::$locale) ) {
			self::$locale = self::getSystemLocale();

			$cookieLocale	= TodoyuLocaleManager::getCookieLocale();
			$browserLocale	= TodoyuBrowserInfo::getBrowserLocale();

			if( TodoyuAuth::isLoggedIn() && self::db()->isConnected() ) {
				$personLocale	= self::person()->getLocale(false);
				if( $personLocale !== false ) {
					self::$locale = $personLocale;
				}
			} elseif( $cookieLocale !== false ) {
				self::$locale	= $cookieLocale;
			} elseif( $browserLocale !== false ) {
				self::$locale = $browserLocale;
			}
		}

		return self::$locale;
	}



	/**
	 * Get base locale defined for system
	 * This is the last fallback locale if not other config is found
	 *
	 * @see		getLocale		For user defined locale
	 * @return	string
	 */
	public static function getSystemLocale() {
		return self::$CONFIG['SYSTEM']['locale'];
	}



	/**
	 * Set system locale with setlocale() based on the currently selected locale
	 *
	 * @param	string|Boolean		$locale			Force locale. If not set try to find the correct locale
	 */
	public static function setLocale($locale = false) {
		if( !$locale ) {
			$locale	= self::getLocale();
		}

			// Set internal locale
		self::$locale = $locale;

			// Set locale for locallang files
		TodoyuLabelManager::setLocale($locale);

			// Set locale for system
		$status	= TodoyuLocaleManager::setSystemLocale($locale);

			// Log if operation fails
		if( !$status ) {
			TodoyuLogger::logError('Can\'t set system locale for "' . $locale . '"');
		}
	}



	/**
	 * Get (EXTID value of) current ext area
	 *
	 * @param	string	$area
	 * @return	integer
	 */
	public static function getArea($area = null) {
		if( is_null($area) ) {
			$area = EXT;
		}

		return TodoyuExtensions::getExtID($area);
	}



	/**
	 * Get area key (string version)
	 *
	 * @return	string
	 */
	public static function getAreaKey() {
		return TodoyuRequest::getArea();
	}



	/**
	 * Add a path to the global include path for autoloading classes
	 *
	 * @param	string		$includePath
	 * @deprecated
	 * @todo	Remove in later version
	 */
	public static function addIncludePath($includePath) {
		TodoyuAutoloader::addPath($includePath);
	}




	/**
	 * Shortcut for TodoyuLabelManager::getLabel()
	 * Get the label in the current language
	 *
	 * @param	string		$labelKey	e.g 'project.status.planning'
	 * @param	string		$locale
	 * @return	string
	 */
	public static function Label($labelKey, $locale = null) {
		return TodoyuLabelManager::getLabel($labelKey, $locale);
	}



	/**
	 * Get person ID. If parameter is not set or 0, get the current person ID
	 *
	 * @param	integer		$idPerson
	 * @return	integer
	 */
	public static function personid($idPerson = 0) {
		$idPerson = (int) $idPerson;

		return $idPerson === 0 ? TodoyuAuth::getPersonID() : $idPerson;
	}


    private static function loadGlobals($smarty){
        /* load date formats */
        if(self::$dates == null) self::$dates = TodoyuTime::loadFormats();
        $smarty->assign('DF', self::$dates);

    }


	/**
	 * Render data with a template
	 * Shortcut for Todoyu Todoyu::tmpl()->get(...);
	 *
	 * @param	string			$template		Path to template file (or a template object)
	 * @param	array			$data			Data for template rendering
	 * @param	boolean	        $is_string		Custom compiler
	 * @param	boolean			$output			Output directly with echo
	 * @return	string			Rendered template
	 */
	public static function render($template, $data = array(), $is_string = false, $output = false) {
		try {

            if($is_string) $template = 'string:'.$template;
            $smarty = self::tmpl();

            $payload = $smarty->createData();
            self::loadGlobals($payload);
            $tpl = $smarty->createTemplate($template);
            foreach($data as $key => $val){
                // if(is_object($val)){
                //     $smarty->registerObject($key, $val);
                // } else {
                    $payload->assign($key, $val);
                // }
            }
            if($output){
                
                $tpl->display($template, $payload);
                return "";
            } else {
                $content = $tpl->fetch($template, $payload);
            }
		} catch(Exception $e) {
			TodoyuHeader::sendTypeText();

			echo "Smarty Template Error: ({$e->getCode()})\n";
			echo "=================================================\n\n";
			echo "Error:	{$e->getMessage()}\n";
			echo "File:		{$e->getFile()} : {$e->getLine()}\n";
			echo "Trace:\n\n";
			echo $e->getTraceAsString();

			exit();
		}

		return $content;
	}



	/**
	 * Check whether a right is set (=allowed)
	 *
	 * @param	string		$extKey		Extension key
	 * @param	string		$right		Right name
	 * @return	boolean
	 */
	public static function allowed($extKey, $right) {
		return TodoyuRightsManager::isAllowed($extKey, $right);
	}



	/**
	 * Check if ALL given rights of an extension are allowed
	 *
	 * @param	string		$extKey			Extension key
	 * @param	string		$rightsList		Comma separated names of rights
	 * @return	boolean
	 */
	public static function allowedAll($extKey, $rightsList) {
		$rights	= explode(',', $rightsList);

		foreach($rights as $right) {
			if( ! self::allowed($extKey, $right) ) {
				return false;
			}
		}

		return true;
	}



	/**
	 * Check if ANY of the given rights of an extension is allowed
	 *
	 * @param	string		$extKey			Extension key
	 * @param	string		$rightsList		Comma separated names of rights
	 * @return	boolean
	 */
	public static function allowedAny($extKey, $rightsList) {
		$rights	= explode(',', $rightsList);

		foreach($rights as $right) {
			if( self::allowed($extKey, $right) ) {
				return true;
			}
		}

		return false;
	}



	/**
	 * Restrict current request to persons who have the right
	 * Stop script if right is not set
	 *
	 * @param	string		$extKey
	 * @param	string		$right
	 */
	public static function restrict($extKey, $right) {
		TodoyuRightsManager::restrict($extKey, $right);
	}



	/**
	 * Restrict access to internal persons
	 */
	public static function restrictInternal() {
		TodoyuRightsManager::restrictInternal();
	}



	/**
	 * Restrict access to admin
	 */
	public static function restrictAdmin() {
		TodoyuRightsManager::restrictAdmin();
	}



	/**
	 * Restrict (deny) access if none if the rights is allowed
	 * If one right is allowed, do nothing
	 *
	 * @param	string		$extKey			Extension key
	 * @param	string		$rightsList		Comma separated names of rights
	 */
	public static function restrictIfNone($extKey, $rightsList) {
		$rights		= explode(',', $rightsList);
		$denyRight	= '';

		foreach($rights as $right) {
			if( self::allowed($extKey, $right) ) {
				return;
			} else {
				$denyRight = $right;
			}
		}

		self::deny($extKey, $denyRight);
	}



	/**
	 * Deny access because of a missing right
	 *
	 * @param	string		$extKey
	 * @param	string		$right
	 */
	public static function deny($extKey, $right) {
		TodoyuRightsManager::deny($extKey, $right);
	}



	/**
	 * Set environment for a person
	 * - Timezone
	 * - Locale
	 *
	 * @param	integer		$idPerson
	 */
	public static function setEnvironmentForPerson($idPerson) {
		self::$environmentBackup = array(
			'locale'	=> self::getLocale(),
			'timezone'	=> self::getTimezone()
		);

		$person	= TodoyuContactPersonManager::getPerson($idPerson);

		TodoyuLabelManager::setLocale($person->getLocale());
		Todoyu::setTimezone($person->getTimezone());
	}



	/**
	 * Reset environment to a previous backup
	 */
	public static function resetEnvironment() {
		if( is_array(self::$environmentBackup) ) {
			TodoyuLabelManager::setLocale(self::$environmentBackup['locale']);
			Todoyu::setTimezone(self::$environmentBackup['timezone']);

			self::$environmentBackup = null;
		}
	}

}

?>