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
 * String helper functions
 *
 * @package		Todoyu
 * @subpackage	Core
 */
class TodoyuString {

	/**
	 * Detect encoding from a string
	 *
	 * @param	string		$string
	 * @return	string		Encoding type (one of the values from the list)
	 */
	public static function getEncoding($string) {
		return mb_detect_encoding($string, 'UTF-16,UTF-8,GBK,ISO-8859-15,ISO-8859-1,ASCII');
	}



	/**
	 * Check whether the given string has an upper-cased first letter
	 *
	 * @param	string		$string
	 * @return	boolean
	 */
	public static function isUcFirst($string) {
		$firstChar	= $string[0];

		return strtoupper($firstChar) == $firstChar;
	}



	/**
	 * Check whether the given string contains HTML tag(s)
	 *
	 * @param	string	$string
	 * @return	boolean
	 */
	public static function isContainingHTML($string) {
		$length			= strlen($string);
		$lengthNoTags	= strlen(strip_tags($string));

		return	$length != $lengthNoTags;
	}



	/**
	 * Check whether a string is utf-8 encoded
	 *
	 * @param	string		$string
	 * @return	boolean
	 */
	public static function isUTF8($string) {
		return self::getEncoding($string) === 'UTF-8';
	}



	/**
	 * Convert a string to UTF-8
	 *
	 * @param	string		$string
	 * @param	string		$fromCharset	Charset to convert from. If not set, we try to auto detect
	 * @return	string						The UTF-8 encoded string
	 */
	public static function convertToUTF8($string, $fromCharset = null) {
		if( is_null($fromCharset) ) {
			$fromCharset = self::getEncoding($string);
		}

		return iconv($fromCharset, 'UTF-8', $string);
	}



	/**
	 * Get string as UTF-8 if it's not already
	 *
	 * @param	string		$string
	 * @return	string
	 */
	public static function getAsUtf8($string) {
		return self::isUTF8($string) ? $string : self::convertToUTF8($string);
	}



	/**
	 * Checking syntax of input email address
	 *
	 * @param	string		$email
	 * @return	boolean		True if the $email is valid: Has a "@", domain name with at least one period and only allowed a-z characters.
	 */
	public static function isValidEmail($email) {
		$email = trim ($email);
		if( strstr($email,' ') ) {
			return false;
		}

		$regexp	= '#^[A-Za-z0-9\._-]+[@][A-Za-z0-9\._-]+[\.].[A-Za-z0-9]+$#';

		return preg_match($regexp, $email) === 1;
	}



	/**
	 * Check whether given string seems to be a valid phone number
	 *
	 * @param	string		$string
	 * @param	string		$allowedChars		Allowed characters (besides numbers)
	 * @return	boolean
	 */
	public static function isValidPhoneNumber($string, $allowedChars = ' /+-().[]') {
		$length	= strlen($string);

		for($i = 0; $i < $length; $i++) {
			$curChar	= substr($string, $i, 1);
			if( !is_numeric($curChar) && strpos($allowedChars, $curChar) === false ) {
				return false;
			}
		}

		return true;
	}



	/**
	 * Crop a text to a specific length. If text is cropped, a postfix will be added (default: ...)
	 * Per default, words will not be split and the text will mostly be a little bit shorter
	 *
	 * @param	string		$text
	 * @param	integer		$length
	 * @param	string		$postFix
	 * @param	boolean		$dontSplitWords
	 * @return	string
	 */
	public static function crop($text, $length, $postFix = '..', $dontSplitWords = true) {
		$length	= (int) $length;

		if( mb_strlen($text, 'utf-8') > $length + mb_strlen($postFix, 'utf-8') ) {
			$cropped	= mb_substr($text, 0, $length, 'utf-8');
			$nextChar	= mb_substr($text, $length, 1, 'utf-8');

				// Go back to last word ending
			if( $dontSplitWords && $nextChar !== ' ' && mb_stristr($cropped, ' ', null, 'utf-8') !== false ) {
				$spacePos	= mb_strrpos($cropped, ' ', 0, 'utf-8');
				$cropped	= mb_substr($cropped, 0, $spacePos, 'utf-8');
			}

				// Remove chars which will be postfixes anyway (prevents 3 points at the end)
			$cropped = rtrim(trim($cropped), substr($postFix, 0, 1));

			$cropped .= $postFix;
		} else {
			$cropped = $text;
		}

		return $cropped;
	}



	/**
	 * Remove all whitespace from given string
	 *
	 * @param	string	$string
	 * @return	string
	 */
	public static function removeAllWhitespace($string) {
		return preg_replace('/\s+/','',$string);
	}



	/**
	 * Wrap string with given pipe-separated wrapper string, e.g. HTML tags
	 *
	 * @param	string	$string
	 * @param	string	$wrap			<tag>|</tag>
	 * @return	string
	 */
	public static function wrap($string, $wrap) {
		return str_replace('|', $string, $wrap);
	}



	/**
	 * Wrap content with a HTML tag
	 *
	 * @param	string		$tag
	 * @param	string		$content
	 * @return	string
	 */
	public static function wrapWithTag($tag, $content) {
		return self::wrap($content, '<' . $tag . '>|</' . $tag . '>');
	}



	/**
	 * Split a camel case formatted string into its words
	 *
	 * @param	string		$string
	 * @return	Array
	 */
	public static function splitCamelCase($string) {
		return preg_split('/([A-Z][^A-Z]*)/', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	}



	/**
	 * Convert an HTML snippet into plain text. Removes html - tags from snippet
	 *
	 * @param	string		$html		HTML snippet
	 * @param	boolean		$decodeEntity
	 * @return	string		Text version
	 */
	public static function html2text($html, $decodeEntity = false) {
		$text	= htmlspecialchars_decode($html);

		$text	= str_replace(array("\n", "\r"), '', $text);
		$text	= self::br2nl($text);
		$text	= str_replace('</p>', "\n\n", $text);
		$text	= str_replace('</li>', "\n", $text);
		$text	= str_replace('<li>', ' - ', $text);
		$text	= strip_tags($text);

		if( $decodeEntity ) {
			$text	= html_entity_decode($text, ENT_COMPAT, 'UTF-8');
		}

		return trim($text);
	}



	/**
	 * Replaces html-tag <br /> with newlines
	 *
	 * @param	string	$string
	 * @return	string
	 */
	public static function br2nl($string) {
		$breaks	= array(
			'<br />',
			'<br/>',
			'<br>',
			'<br >'
		);

		return str_ireplace($breaks, "\n", $string);
	}



	/**
	 * Get a substring around a keyword
	 *
	 * @param	string		$string			The whole text
	 * @param	string		$keyword		Keyword to find in the text
	 * @param	integer		$charsBefore	Characters included before the keyword
	 * @param	integer		$charsAfter		Characters included after the keyword
	 * @param	boolean		$htmlEntities
	 * @return	string		Substring with keyword surrounded by the original text
	 */
	public static function getSubstring($string, $keyword, $charsBefore = 20, $charsAfter = 20, $htmlEntities = true) {
		$charsBefore= (int) $charsBefore;
		$charsAfter	= (int) $charsAfter;
		$keyLen		= mb_strlen(trim($keyword));
		$pos		= mb_stripos($string, $keyword);
		$start		= TodoyuNumeric::intInRange($pos-$charsBefore, 0);
		$subLen		= $charsBefore + $keyLen + $charsAfter;

		if( $htmlEntities ) {
			$string	= html_entity_decode($string, ENT_QUOTES, 'UTF-8');
		}

		$string = mb_substr($string, $start, $subLen);

		if( $htmlEntities ) {
			$string = htmlentities($string, ENT_QUOTES, 'UTF-8', false);
		}

		return trim($string);
	}



	/**
	 * Check whether the given string has the given beginning
	 *
	 * @param   String  $string
	 * @param   String  $start
	 * @return  boolean
	 */
	public static function startsWith($string, $start) {
		$lenStart  = strlen($start);

		return substr($string, 0, $lenStart) === $start;
	}



	/**
	 * Check whether the given string has the given ending
	 *
	 * @param   String  $string
	 * @param   String  $ending
	 * @return  boolean
	 */
	public static function endsWith($string, $ending) {
		$lenEnding  = strlen($ending);

		return substr($string, strlen($string) - $lenEnding) === $ending;
	}



	/**
	 * Add an element to a separated list (ex: coma separated)
	 *
	 * @param	string		$list
	 * @param	string		$value
	 * @param	string		$separator
	 * @param	boolean		$unique
	 * @return	string
	 */
	public static function addToList($list, $value, $separator = ',', $unique = false) {
		$items	= explode($separator, $list);
		$items[]= $value;

		if( $unique ) {
			$items = array_unique($items);
		}

		return implode($separator, $items);
	}



	/**
	 * Check if an element is in a separated list string (ex: comma separated)
	 *
	 * @param	string		$item				Element to check for
	 * @param	string		$listString			List with concatenated elements
	 * @param	string		$listSeparator		List element separating character
	 * @return	boolean
	 */
	public static function isInList($item, $listString, $listSeparator = ',') {
		$list	= explode($listSeparator, $listString);

		return in_array($item, $list);
	}



	/**
	 * Remove duplicate entries from list
	 *
	 * @param	string	$listString
	 * @param	string	$listSeparator
	 * @return	string
	 */
	public static function listUnique($listString, $listSeparator = ',') {
		$list = TodoyuArray::trimExplode($listSeparator, $listString);
		$list = array_unique($list);

		return implode($listSeparator, $list);
	}



	/**
	 * Generate a random password. Customizable
	 *
	 * @param	integer		$length
	 * @param	boolean		$useUpperCase
	 * @param	boolean		$useNumbers
	 * @param	boolean		$useSpecialChars
	 * @param	boolean		$useDoubleChars
	 * @return	string
	 */
	public static function generatePassword($length = 8, $useUpperCase = true, $useNumbers = true, $useSpecialChars = true, $useDoubleChars = true) {
		$length		= (int) $length;
		$characters	= range('a', 'z');

		if( $useUpperCase ) {
			$characters = array_merge($characters, range('A', 'Z'));
		}
		if( $useNumbers ) {
			$characters = array_merge($characters, range('0', '9'));
		}
		if( $useSpecialChars ) {
			$characters = array_merge($characters, array('#','&','@','$','_','%','?','+','-'));
		}
		if( $useDoubleChars ) {
			shuffle($characters);
			$characters = array_merge($characters, $characters);
		}

			// Shuffle array
		shuffle($characters);
		$password = substr(implode('', $characters), 0, $length);

		return $password;
	}



	/**
	 * @return	string
	 */
	public static function generateGoodPassword() {
		$config		= Todoyu::$CONFIG['SETTINGS']['passwordStrength'];
		$validator	= new TodoyuPasswordValidator();

		do {
			$password = self::generatePassword( $config['minLength'],
												$config['hasUpperCase'],
												$config['hasNumbers'],
												$config['hasSpecialChars']);

		} while( !$validator->validate($password) );

		return $password;
	}



	/**
	 * Format a file size in the GB/MB/KB/B and add label
	 *
	 * @param	integer		$fileSize
	 * @param	array		$labels			Custom label array (overrides the default labels
	 * @param	boolean		$noLabel		Don't append label
	 * @return	string
	 */
	public static function formatSize($fileSize, array $labels = null, $noLabel = false) {
			// Have to use floatval instead of intval because of the max range of integer supports only for up to 2,5GB..
		$fileSize	= round(floatval($fileSize), 0);

		if( is_null($labels) ) {
			if( !$noLabel ) {
				$labels = array(
					'gb'	=> Todoyu::Label('core.file.size.gb'),
					'mb'	=> Todoyu::Label('core.file.size.mb'),
					'kb'	=> Todoyu::Label('core.file.size.kb'),
					'b'		=> Todoyu::Label('core.file.size.b')
				);
			} else {
				$labels	= array();
			}
		}

			// Add applicable size label (GB / MB / KB / B)
		if( $fileSize > TodoyuNumeric::BYTES_GIGABYTE ) {			// GB
			$size	= $fileSize / TodoyuNumeric::BYTES_GIGABYTE;
			$label	= $labels['gb'];
		} elseif( $fileSize > TodoyuNumeric::BYTES_MEGABYTE ) {		// MB
			$size	= $fileSize / TodoyuNumeric::BYTES_MEGABYTE;
			$label	= $labels['mb'];
		} elseif( $fileSize > TodoyuNumeric::BYTES_KILOBYTE ) {		// KB
			$size	= $fileSize / TodoyuNumeric::BYTES_KILOBYTE;
			$label	= $labels['kb'];
		} else {													// B
			$size	= $fileSize;
			$label	= $labels['b'];
		}

			// Show only a decimal when smaller then 10
		$dez	= $size >= 10 ? 0 : 1;
		$size	= round($size, $dez);

		return number_format($size, $dez, '.', '') . ( $noLabel ? '' : ' ' . $label);
	}



	/**
	 * Wrap into JS tag
	 *
	 * @param	string	$jsCode
	 * @return	string
	 */
	public static function wrapScript($jsCode) {
		return '<script type="text/javascript">' . $jsCode . '</script>';
	}



	/**
	 * Build an URL with given parameters prefixed with todoyu path
	 *
	 * @param	array		$params			Parameters as key=>value
	 * @param	string		$hash			Hash (#hash)
	 * @param	boolean		$absolute		Absolute URL with host server
	 * @param	boolean		$dontEncode		Don't encode html entities (use & instead of &amp; as argument separator)
	 * @return	string
	 */
	public static function buildUrl(array $params = array(), $hash = '', $absolute = false, $dontEncode = false) {
		$query			= '/' . ltrim(PATH_WEB . '/index.php', '/');
		$argSeparator	= $dontEncode ? '&' : '&amp;';

			// Add question mark if there are query parameters
		if( sizeof($params) > 0 ) {
			$query .= '?';
		}

			// Add all parameters encoded
		$query .= http_build_query($params, null, $argSeparator);

			// Add hash
		if( ! empty($hash) ) {
			$query .= '#' . $hash;
		}

			// Add absolute server URL
		if( $absolute ) {
			$query = SERVER_URL . '/' . ltrim($query, '/');
		}

		return $query;
	}



	/**
	 * Get short md5 hash of a string
	 *
	 * @param	string		$string
	 * @return	string		10 characters MD5 hash value of the string
	 */
	public static function md5short($string) {
		return substr(md5($string), 0, 10);
	}



	/**
	 * Analyze version string and return array of contained sub versions and attributes
	 *
	 * @param	string		$versionString
	 * @return	Array		[major,minor,revision,status]
	 */
	public static function getVersionInfo($versionString) {
		$info			= array();

		if( strpos($versionString, '-') !== false ) {
			$temp	= explode('-', $versionString);
			$version= explode('.', $temp[0]);
			$status	= $temp[1];
		} else {
			$version= explode('.', $versionString);
			$status	= 'stable';
		}

		$info['full']		= $versionString;
		$info['major']		= (int) $version[0];
		$info['minor']		= (int) $version[1];
		$info['revision']	= (int) $version[2];
		$info['status']		= $status;

		return $info;
	}



	/**
	 * Explode string and trim the parts
	 * Alias of TodoyuArray::trimExplode()
	 *
	 * @see		TodoyuArray::trimExplode()
	 * @param	string		$delimiter
	 * @param	string		$string
	 * @param	boolean		$removeEmptyValues
	 * @return	Array
	 */
	public static function trimExplode($delimiter, $string, $removeEmptyValues = false) {
		return TodoyuArray::trimExplode($delimiter, $string, $removeEmptyValues);
	}



	/**
	 * Extract the headers from a full HTTP response (including headers and content)
	 *
	 * @param	string		$responseContent
	 * @return	Array
	 */
	public static function extractHttpHeaders($responseContent) {
			// Split header and content
		list($headerString) = explode("\r\n\r\n", $responseContent);

		return self::extractHeadersFromString($headerString);
	}



	/**
	 * Extract header pairs from a HTTP header string
	 *
	 * @param	string		$headerString
	 * @return	Array		array
	 */
	public static function extractHeadersFromString($headerString) {
			// Split header pairs
		$headerPairs= explode("\r\n", $headerString);
		$headers	= array();

			// Add HTTP status as status key
		$headers['status']		= array_shift($headerPairs);
		$headers['statusCode']	= self::extractHttpStatusCode($headers['status']);

			// Add the rest of the header pairs
		foreach($headerPairs as $headerPair) {
			list($key, $value) = explode(':', $headerPair, 2);
			$headers[trim($key)] = trim($value);
		}

		return $headers;
	}



	/**
	 * Extract status code from http status header
	 *
	 * @param	string		$httpStatusHeader
	 * @return	integer
	 */
	public static function extractHttpStatusCode($httpStatusHeader) {
		$parts	= explode(' ', $httpStatusHeader);

		return (int) $parts[1];
	}



	/**
	 * Find registered linkable elements in given text and substitutes them by HTML hyperlinks
	 *
	 * @param	string	$htmlContent
	 * @return	string
	 */
	public static function substituteLinkableElements($htmlContent) {
		$hooks	= TodoyuHookManager::getHooks('core', 'substituteLinkableElements');

		foreach($hooks as $funcRef) {
			if( TodoyuFunction::isFunctionReference($funcRef) ) {
				$htmlContent	= TodoyuFunction::callUserFunction($funcRef, $htmlContent);
			}
		}

		return $htmlContent;
	}



	/**
	 * Takes a clear text message, finds all URLs and substitutes them by HTML hyperlinks
	 *
	 * @param	string	$htmlContent	Message content
	 * @return	string
	 */
	public static function replaceUrlWithLink($htmlContent) {
				// Find full links with prefixed protocol
		$patternFull	= '/(^|[^"\(])((?:http|https|ftp|ftps):\/\/[-\w@:!%+.~#?&;\/=\[\]]+)/is';
		$replaceFull	= '\1<a href="\2" target="_blank">\2</a>';

			// Find links which are not prefixed with a protocol, use http
		$patternSimple	= '/(^|[> ])((?:[\w\.-]+)\.(?:[\w-]{2,})\.(?:[a-zA-Z-]{2,6})[-\w@:!%+.~#?&;\/=\[\]]*)/is';
		$replaceSimple	= '\1<a href="http://\2" target="_blank">\2</a>';

			// Find mailto links
		$patternEmail	= "/(?<before>mailto:)(?<completeTag><(?<tagOpen>\w+)(?<tagattributes>[^>]?)*>)?(?<content>[-\w\.]+@[-\w\.]+)(?<tagClose><\/\2>)?/";

			// Replace URLs
		$htmlContent	= preg_replace($patternFull, $replaceFull, $htmlContent);
		$htmlContent	= preg_replace($patternSimple, $replaceSimple, $htmlContent);
		$htmlContent	= preg_replace_callback($patternEmail, array("TodoyuString","replaceEmailInText"), $htmlContent);

		return $htmlContent;
	}



	/**
	 * Add linking for email addresses
	 * 
	 * @param	array	$matches
	 * @return	string
	 */
	public static function replaceEmailInText($matches) {
		$replaceEmail	= '<a href="mailto:%s">%s</a>';

			// Ignore already linked elements
		if( $matches['before'] === 'mailto:' ) {
			return $matches[0];
		}

		if( $matches['completeTag'] === '' ) {
			return sprintf($replaceEmail, $matches['content'], $matches['content']);
		} else if( $matches['tagOpen'] !== 'a' ) {
			return $matches['completeTag'] . sprintf($replaceEmail, $matches['content'], $matches['content']) . $matches['tagClose'];
		}

		return $matches[0];
	}



	/**
	 * Clean RTE text
	 *  - Remove empty paragraphs from the beginning
	 *  - Remove <pre> tags and add <br> tags for the newlines
	 *
	 * @param	string		$html
	 * @return	string
	 */
	public static function cleanRTEText($html) {
		if( substr($html, 0, 13) === '<p>&nbsp;</p>' ) {
			$html	= substr($html, 13);
		}

			// Fix problem with <pre> tags from RTE
		$html	= self::cleanPreTagsInRTE($html);
			// Remove event handler attributes to prevent XSS
		$html	= self::cleanXssTagAttributes($html);

		return trim($html);
	}



	/**
	 * Remove <pre> tags and add <br> tags for line breaks
	 *
	 * @param	string		$html
	 * @return	string
	 */
	private static function cleanPreTagsInRTE($html) {
		if( strpos($html, '<pre>') !== false ) {
			$pattern	= '/<pre>(.*?)<\/pre>/s';
			$html		= preg_replace_callback($pattern, array('TodoyuString','callbackPreText'), $html);
			$html		= str_replace("\n", '', $html);
		}

		return $html;
	}



	/**
	 * Callback for cleanRTEText
	 * Add <br> tags inside the <pre> tags
	 *
	 * @param	array		$match
	 * @return	string
	 */
	private static function callbackPreText(array $match) {
		return nl2br(trim($match[1]));
	}



	/**
	 * Cleanup for XSS in tag attributes (onclick, ...)
	 *
	 * @param	string		$html
	 * @return	string
	 */
	private static function cleanXssTagAttributes($html) {
		$pattern	= '/<(?:.+?)( on(?:\w{4,})=(["\'])(?:.*?)[^\\]\2)(?:[^>]*?)>/';
		$html		= preg_replace_callback($pattern, array('TodoyuString','callbackXssTagAttributes'), $html);

		return $html;
	}



	/**
	 * Callback for XSS attribute cleanup
	 * Replace event handler attributes from tags
	 *
	 * @param	array	$match
	 * @return	string
	 */
	private static function callbackXssTagAttributes(array $match) {
		return str_replace($match[1], '', $match[0]);
	}



	/**
	 * Returns an HTML <a href="mailto:"> - tag
	 *
	 * @param	string	$emailAddress
	 * @param	string	$label
	 * @param	boolean	$returnAsArray
	 * @param	string	$subject
	 * @param	string	$mailBody
	 * @param	string	$cc
	 * @param	string	$bcc
	 * @return	string
	 */
	public static function buildMailtoATag($emailAddress, $label = '', $returnAsArray = false, $subject = '', $mailBody = '', $cc ='', $bcc = '') {
		$linkParts	= array();

		if( $subject ) {
			$linkParts[] = 'subject=' . urlencode($subject);
		}
		if( $mailBody ) {
			$linkParts[] = 'body=' . urlencode($mailBody);
		}
		if( $cc ) {
			$linkParts[] = 'cc=' . $cc;
		}
		if( $bcc ) {
			$linkParts[] = 'ccc=' . $bcc;
		}

		$url				= 'mailto:' . $emailAddress . '?' . implode('&', $linkParts);

		if( $label === '' ) {
			$label = $emailAddress;
		}

		$aTag	= self::buildATag($url, $label);

		if( $returnAsArray ) {
			return array(
				str_replace('</a>', '', $aTag),
				'</a>'
			);
		} else {
			return $aTag;
		}
	}



	/**
	 * Returns an HTML (anchor) link tag
	 *
	 * @param	string	$url
	 * @param	string	$label
	 * @param	string	$target
	 * @return	string
	 */
	public static function buildATag($url, $label, $target = '') {
		$attributes	= array(
			'href'	=> $url
		);

		if( $target ) {
			$attributes['target'] = $target;
		}

		return self::buildHtmlTag('a', $attributes, $label);
	}



	/**
	 * Wrap given label with a todoyu-internal link if given ext is allowed to be used
	 *
	 * @param	string		$label
	 * @param	string		$extKey
	 * @param	array		$params
	 * @param	string		$hash
	 * @param	string		$target
	 * @return	string
	 */
	public static function wrapTodoyuLink($label, $extKey, array $params = array(), $hash = '', $target = '') {
			// Check extension's general right setting
		if( Todoyu::allowed($extKey, 'general:use') ) {
			if( !isset($params['ext']) ) {
				$params['ext'] = $extKey;
			}

			$attributes = array(
				'href'	=> self::buildUrl($params, $hash)
			);

			if( !empty($target) ) {
				$attributes['target'] = $target;
			}

			$label	= self::buildHtmlTag('a', $attributes, $label);
		}

		return $label;
	}



	/**
	 * Returns an HTML - img tag
	 *
	 * @param	string		$src
	 * @param	integer		$width
	 * @param	integer		$height
	 * @param	string		$altText
	 * @return	string
	 */
	public static function getImgTag($src, $width = 0, $height = 0, $altText = '') {
		$attributes	= array();
		$width		= (int) $width;
		$height		= (int) $height;
		$altText	= trim($altText);

		$attributes['src'] = $src;

		if( $width > 0 ) {
			$attributes['width']	= $width;
		}
		if( $height > 0 ) {
			$attributes['height']	= $height;
		}
		if( $altText !== '' ) {
			$attributes['alt']	= $altText;
		}

		return self::buildHtmlTag('img', $attributes);
	}



	/**
	 * Build a HTML tag with attributes
	 *
	 * @param	string			$tagName
	 * @param	array			$attributes
	 * @param	string|Boolean	$content			Optional HTML content to be wrapped
	 * @return	string
	 */
	public static function buildHtmlTag($tagName, array $attributes = array(), $content = false) {
		$attr	= array();

		foreach($attributes as $name => $value) {
			$attr[]	= $name . '="' . $value . '"';
		}

		$attrList	= implode(' ', $attr);

		if( $content !== false ) {
			return '<' . $tagName . ' ' . $attrList . '>' . $content . '</' . $tagName . '>';
		} else {
			return '<' . $tagName . ' ' . $attrList . ' />';
		}
	}



	/**
	 * Replace quotes around string which contain a function
	 * Allows to add javascript functions in JSON encoded content
	 *
	 * Used code posted here:
	 * http://tipstank.com/2010/10/29/how-to-add-javascript-function-expression-and-php-json_encode/
	 *
	 * @param	string		$json
	 * @return	string
	 */
	public static function enableJsFunctionInJSON($json) {
		$pattern	= '/(?<=:)"function\((?:(?!}").)*}"/';
		$callback	= array('TodoyuString', 'escapeFunctionInJSON');

		return preg_replace_callback($pattern, $callback, $json);
	}



	/**
	 * Callback for enableJsFunctionInJSON to replace quotes around function expressions
	 *
	 * @param	string		$string
	 * @return	string
	 */
	private static function escapeFunctionInJSON($string) {
		return str_replace('\\"','\"',substr($string[0],1,-1));
	}



	/**
	 * Convert a variable to it's PHP string representation
	 *
	 * @param	Mixed		$value
	 * @return	string
	 * @deprecated
	 */
	public static function toPhpCodeString($value) {
		return self::toPhpCode($value);
	}


	/**
	 * Convert a value to it's php code representation
	 *
	 * @param	Mixed		$value
	 * @return	string
	 */
	public static function toPhpCode($value) {
		switch(gettype($value)) {
			case 'integer';
			case 'double';
				break;
			case 'string':
				$value = '\'' . addslashes($value) . '\'';
				break;
			case 'NULL':
				$value = 'null';
				break;
			case 'boolean':
				$value = $value ? 'true' : 'false';
				break;
			case 'array':
				$value = self::toPhpCodeArray($value);
				break;
//			case 'object':
			default:
				$value = 'unserialize(\'' . serialize($value) . '\')';
				break;
		}

		return $value;
	}



	/**
	 * Convert an array to it's php code representation
	 *
	 * @param	array		$array
	 * @return	string
	 */
	public static function toPhpCodeArray(array $array) {
		$pairs	= array();

		foreach($array as $key => $value) {
			$pairs[] = self::toPhpCode($key) . '=>' . self::toPhpCode($value);
		}

		return 'array(' . implode(',', $pairs) . ')';
	}



	/**
	 * htmlentities with predefined config for todoyu
	 *
	 * @param	string		$string
	 * @param	boolean		$doubleEncode
	 * @return	string
	 */
	public static function htmlentities($string, $doubleEncode = false) {
		return htmlentities($string, ENT_QUOTES, 'UTF-8', $doubleEncode);
	}



	/**
	 * Remove every path info or file extensions
	 * Make sure you have a simple string without any path or file information
	 * (which could cause an attack)
	 *
	 * @param	string		$pathString
	 * @return	string
	 */
	public static function removePathParts($pathString) {
		return pathinfo($pathString, PATHINFO_FILENAME);
	}



	/**
	 * Replace quotes with numeric ascii values
	 * " => \042
	 * ' => \047
	 *
	 * @param	string		$string
	 * @return	string
	 */
	public static function escapeQuotesForHtmlAttributes($string) {
		return str_replace(array("'", '"'), array('\047', '\042'), $string);
	}

}

?>