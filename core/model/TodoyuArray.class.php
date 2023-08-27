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
 * Array helper functions
 *
 * @package		Todoyu
 * @subpackage	Core
 */
class TodoyuArray {

	/**
	 * Extract a single field from a array
	 *
	 * @param	array		$array
	 * @param	string		$columnName
	 * @return	Array
	 */
	public static function getColumn(array $array, $columnName) {
		$column = array();

		foreach($array as $subArray) {
			$column[] = $subArray[$columnName];
		}

		return $column;
	}



	/**
	 * Get unique values of a column
	 *
	 * @param	array		$array
	 * @param	string		$columnName
	 * @return	Array
	 */
	public static function getColumnUnique(array $array, $columnName) {
		return array_unique(self::getColumn($array, $columnName));
	}



	/**
	 * Get first key of associative array
	 *
	 * @param	array	$array
	 * @return	string
	 */
	public static function getFirstKey($array) {
		reset($array);

		return key($array);
	}



	/**
	 * Get key of last element in associative array
	 *
	 * @param	array	$array
	 * @return	string
	 */
	public static function getLastKey($array) {
		end($array);

		return key($array);
	}



	/**
	 * Convert all array values to integers. This means all 'non-integer' will be 0
	 * If $onlyPositive is true, all negative integers will be zero too
	 * If $onlyPositive and $removeZeros are true, new array will contain only positive integers
	 *
	 * @param	array		$array			Dirty array
	 * @param	boolean		$onlyPositive	Set negative values zero
	 * @param	boolean		$removeZeros	Remove all zero values
	 * @return	integer[]	Integer array
	 */
	public static function intval($array, $onlyPositive = false, $removeZeros = true) {
		if( ! is_array($array) ) {
			return array();
		}

			// Make integers
		foreach($array as $index => $value) {
			$array[$index] = (int) $value;
		}

			// Set negative values zero
		if( $onlyPositive ) {
			foreach($array as $index => $value) {
				if( $value < 0 ) {
					$array[$index] = 0;
				}
			}
		}

			// Remove zeros
		if( $removeZeros ) {
			$newArray = array();
			foreach($array as $index => $value) {
				if( $value != 0 ) {
					$newArray[$index] = $value;
				}
			}
			$array = $newArray;
		}

		return array_values($array);
	}



	/**
	 * Apply floatval to all array elements
	 *
	 * @param	array		$array
	 * @return	Array
	 */
	public static function floatval(array $array) {
		return array_map('floatval', $array);
	}



	/**
	 * Apply strtolower to all array elements
	 *
	 * @param	array		$array
	 * @return	Array
	 */
	public static function strtolower(array $array) {
		return array_map('strtolower', $array);
	}



	/**
	 * Implode array with given delimiter, force items to be integers
	 *
	 * @param	array	$array
	 * @param	string	$delimiter
	 * @return	string
	 */
	public static function intImplode($array = array(), $delimiter = ',') {
		foreach($array as $id => $value) {
			$array[$id] = (int) $value;
		}

		return implode($delimiter, $array);
	}



	/**
	 * Rename key of an array, defined by mapping array. Only mapped keys will be in the reformed array
	 *
	 * @param	array	$array
	 * @param	array	$reformConfig		[old=>new,old=>new]
	 * @param	boolean	$copyAllData
	 * @return	Array
	 */
	public static function reform(array $array, array $reformConfig, $copyAllData = false) {
		$reformedArray	= array();

		foreach($array as $item) {
			$tempItem = $copyAllData ? $item : array();
			foreach($reformConfig as $oldKey => $newKey) {
				$tempItem[$newKey] = $item[$oldKey];
			}
			$reformedArray[] = $tempItem;
		}

		return $reformedArray;
	}



	/**
	 * Rename key of array by given mapping array (see TodoyuArray::reform()), use given field as new index for sub arrays
	 *
	 * @param	array	$array
	 * @param	array	$reformConfig
	 * @param	boolean	$copyAllData
	 * @param	string	$indexFieldName
	 * @return	Array
	 */
	public static function reformWithFieldAsIndex(array $array, array $reformConfig, $copyAllData = false, $indexFieldName) {
		$array	= self::reform($array, $reformConfig, $copyAllData);

		return self::useFieldAsIndex($array, $indexFieldName);
	}



	/**
	 * Stripslashes on all array values and sub arrays
	 *
	 * @param	array	$array
	 * @return	Array
	 */
	public static function stripslashes(array $array) {
		foreach($array as $key => $value) {
			if( is_array($value) ) {
				$array[$key] = self::stripslashes($value);
			} else {
				$array[$key] = stripslashes($value);
			}
		}

		return $array;
	}



	/**
	 * Sort an array by a specified label. Allows advanced sorting configuration
	 *
	 * @param	array		$unsortedArray			Original array
	 * @param	string		$sortByLabel			Label key to sort by
	 * @param	boolean		$reversed				Reverse order
	 * @param	boolean		$caseSensitive			Sort case sensitive. Lower case string are sorted as extra group at the end
	 * @param	boolean		$useNaturalSorting		Sort as a human would do. Ex: Image1, Image2, Image 10, Image20
	 * @param	integer		$sortingFlag			Flag for normal (not natural) sorting. Use constants: SORT_NUMERIC, SORT_STRING, SORT_LOCALE_STRING
	 * @param	string		$avoidDuplicateFieldKey
	 * @return	Array		Sorted array
	 */
	public static function sortByLabel(array $unsortedArray, $sortByLabel = 'position', $reversed = false, $caseSensitive = false, $useNaturalSorting = true, $sortingFlag = SORT_REGULAR, $avoidDuplicateFieldKey = '') {
		if( sizeof($unsortedArray) === 0 ) {
			return $unsortedArray;
		}

					// Use the labels as key
			// Prevent overwriting double labels
		$labelKeyArray		= array();
		$conflictCounter	= 0;

		foreach($unsortedArray as $index => $item) {

			$label	= isset( $item[$sortByLabel] ) ? $item[$sortByLabel] : NULL;
			if( $caseSensitive !== true ) {
				$label	=	strtolower($label);
			}

			$key	= array_key_exists($label, $labelKeyArray) ? $label . '-' . $conflictCounter++ : $label;

			$item['__key']	= $index;

			$labelKeyArray[$key]	= $item;
		}

			// If no natural sorting is needed, we can take the built-in functions
		if( !$useNaturalSorting ) {
			if( $reversed ) {
				krsort($labelKeyArray, $sortingFlag);
			} else {
				ksort($labelKeyArray, $sortingFlag);
			}

				// Filter for duplicate field contents,  if requested
			if( $avoidDuplicateFieldKey != '' ) {
				$labelKeyArray = self::removeDuplicates($labelKeyArray, $avoidDuplicateFieldKey);
			}

			$sortedArray = array_values($labelKeyArray);
		} else {
				// Natural sorting
			$labels	= array_keys($labelKeyArray);
			natsort($labels);

				// Reverse keys if requested
			if( $reversed ) {
				$labels = array_reverse($labels);
			}

				// Load items in the new order into a new array
			$sortedArray = array();
			foreach($labels as $label) {
				$sortedArray[] = $labelKeyArray[$label];
			}
		}

		return $sortedArray;
	}



	/**
	 * Get a new array which contains only elements that match the filter. The field value has to be in the list of the filter item
	 *
	 * @example
	 *
	 * Only keep items which have a uid between 1 and 9 AND have 352, 80, 440 or 240 pages
	 *
	 * $products	= Todoyu::db()->getArray('*', 'ext_shop_products');
	 * $filter		= array('uid'	=> array(1,2,3,4,5,6,7,8,9),
	 *						'pages'	=> array(352,80,440,240));
	 * $filteredProducts = TodoyuArray::filter($prodcuts, $filter);
	 *
	 *
	 * @param	array		$dataArray			Array with the element which are checked against the filter
	 * @param	array		$filterArray		The filter. It's elements are the field names with the allowed values
	 * @param	boolean		$matching			Normally you'll get the matching elements. FALSE gives you the elements which don't match
	 * @param	boolean		$preserveKeys		Keep the array keys. Else they will be replaced by numeric keys
	 * @return	Array		The filtered array
	 */
	public static function filter(array $dataArray, array $filterArray, $matching = true, $preserveKeys = false ) {
		$passed = array();

			// Check each item
		foreach($dataArray as $key => $itemArray) {
			$match = true;

				// Check if all filters succeed. Stop if any fails
			foreach($filterArray as $fieldName => $allowedValues) {
				if( ! in_array($itemArray[$fieldName], $allowedValues) ) {
					$match = false;
					break;
				}
			}

				// Add to result if the matching is the same as requested
			if( $match === $matching ) {
				$passed[$key] = $itemArray;
			}
		}

		return $preserveKeys ? $passed : array_values($passed);
	}



	/**
	 * Prefix values
	 *
	 * @param	array	$array
	 * @param	string	$prefix
	 * @param	string	$postfix
	 * @return	Array
	 * @deprecated
	 */
	public static function prefix(array $array, $prefix, $postfix = '') {
		return self::prefixValues($array, $prefix, $postfix);
	}



	/**
	 * Prefix array value with a string. PostFix is also available
	 *
	 * @param	array		$array
	 * @param	string		$prefix
	 * @param	string		$postfix
	 * @return	Array
	 */
	public static function prefixValues(array $array, $prefix, $postfix = '') {
		foreach($array as $index => $value) {
			$array[$index] = $prefix . $value . $postfix;
		}

		return $array;
	}



	/**
	 * Prefix/postfix array keys
	 * Example:
	 * a: 1, b:2, c:3 => prefixKeys(..,'test_'); => test_a:1, test_b:2, test_c:3
	 *
	 * @param	array		$array
	 * @param	string		$prefix
	 * @param	string		$postfix
	 * @return	Array
	 */
	public static function prefixKeys(array $array, $prefix, $postfix = '') {
		$newArray = array();

		foreach($array as $index => $value) {
			$newArray[$prefix . $index . $postfix] = $value;
		}

		return $newArray;
	}



	/**
	 * Insert an element into an associative array.
	 * The base array should have named keys. Insert position can be defined by $beforeItem.
	 * If an element with the specified key already exists, it will be replace, except if $replace is false.
	 *
	 * @param	array		$array			Base array to insert new item into
	 * @param	string		$newKeyName		Key name of the new array item
	 * @param	Mixed		$newItem
	 * @param	string		$mode			'after' / 'before'
	 * @param	string		$refKeyName		Key name of field the mode refers to
	 * @param	boolean		$replace		Replace an existing element
	 * @return	Array		Array with new item inside
	 */
	public static function insertElement(array $array, $newKeyName, $newItem, $mode = 'after', $refKeyName = null, $replace = true) {
		$mode		= $mode === 'after' ? 'after' : 'before';
		$exists		= array_key_exists($refKeyName, $array);
		$refKeyName	= $exists ? $refKeyName : null;
		$newArray	= array();

			// Remove current element if it already exists and replace is set true
		if( $exists && $replace && $newKeyName === $refKeyName ) {
			unset($array[$refKeyName]);
		}

			// Stop here if key exists and replacing is disabled
		if( $exists && !$replace && $newKeyName === $refKeyName ) {
				// No action if element already exists and replacing is disabled
			$newArray =& $array;
//			TodoyuDebug::printHtml($array);
		} else {
				// If no reference set and mode is before, insert as first element
			if( $mode === 'before' && $refKeyName === null ) {
				$newArray[$newKeyName] = $newItem;
			}

			foreach($array as $key => $item) {
					// When insert reference element found
				if( $key === $refKeyName ) {
						// Insert new element before
					if( $mode === 'before' ) {
						$newArray[$newKeyName] = $newItem;
					}
						// Insert element
					$newArray[$key] = $item;
						// Insert new element after
					if( $mode === 'after' ) {
						$newArray[$newKeyName] = $newItem;
					}
				} else {
						// Normal key copy
					$newArray[$key] = $item;
				}
			}

				// If no reference set and mode is after, insert as last element
			if( $mode === 'after' && $refKeyName === null ) {
				$newArray[$newKeyName] = $newItem;
			}
		}

		return $newArray;
	}



	/**
	 * Convert a SimpleXmlElement structure to an associative array. All objects are casted to array
	 *
	 * @param	SimpleXmlElement	$xml	XML object or array (which possibility contains XML objects)
	 * @return	Array
	 */
	public static function fromSimpleXml($xml) {
		$array = (array)$xml;

		foreach($array as $index => $value) {
			if( $value instanceof SimpleXMLElement || is_array($value) ) {
				$array[$index] = self::fromSimpleXML($value);
			}
		}

		return $array;
	}



	/**
	 * Convert an object to an array (recursively)
	 * Basic types are ignored
	 *
	 * @param	Mixed		$element
	 * @param	boolean		$recursive
	 * @return	Array
	 */
	public static function toArray($element, $recursive = true) {
			// Return element if not complex value
		if( gettype($element) !== 'array' && gettype($element) !== 'object' ) {
			return $element;
		}

			// Convert object to array by casting
		if( gettype($element) === 'object' ) {
			$element = (array)$element;
		}

			// Convert recursively
		if( $recursive ) {
			foreach($element as $index => $value) {
				$element[$index] = self::toArray($value, true);
			}
		}

		return $element;
	}



	/**
	 * Remove data if key matches with one in $keysToRemove
	 *
	 * @param	array		$array					The array with the data
	 * @param	array		$keysToRemove			Array which contains the key to remove. Ex: [userFunc,config,useless]
	 * @return	Array		Array which doesn't contain the specified keys
	 */
	public static function removeKeys($array, array $keysToRemove) {
		foreach($keysToRemove as $keyToRemove) {
			unset($array[$keyToRemove]);
		}

		return $array;
	}



	/**
	 * Implode array and wrap all entries into single/double quotes
	 *
	 * @param	array		$array				Items
	 * @param	string		$delimiter			Implode delimiter
	 * @param	boolean		$useDoubleQuotes	Use double quotes (") instead of single quotes (')
	 * @return	Array
	 */
	public static function implodeQuoted(array $array, $delimiter = ',', $useDoubleQuotes = false) {
		$quote	= $useDoubleQuotes ? '"' : "'";

		$array	= self::wrapItems($array, $quote, $quote, 'addslashes');

		return implode($delimiter, $array);
	}



	/**
	 * Implode associative array
	 *
	 * Example:
	 * $data = array('top'=>200, 'left'=>300)
	 * implodeAssoc($data, ':', ';')
	 * => top:200;left:300
	 *
	 * @param	array	$array
	 * @param	string	$keyValueSeparator
	 * @param	string	$itemSeparator
	 * @return	string
	 */
	public static function implodeAssoc(array $array, $keyValueSeparator, $itemSeparator) {
		$inner	= array();

		foreach($array as $key => $value) {
			$inner[] = $key . $keyValueSeparator . $value;
		}

		return implode($itemSeparator, $inner);
	}



	/**
	 * Wrap all items of an array. Add a string before and after the value.
	 * Only strings and numbers are wrapped, everything else will not be touched.
	 * If $wrapAfter is false, $wrapBefore is used
	 *
	 * @param	array				$array
	 * @param	string				$wrapBefore
	 * @param	string|Boolean		$wrapAfter
	 * @param	string|Boolean		$callback		Optional callback function which is applied to all processed items before wrapped. Ex: addslashes
	 * @return	Array
	 */
	public static function wrapItems(array $array, $wrapBefore, $wrapAfter = false, $callback = false) {
		if( $wrapAfter === false ) {
			$wrapAfter = $wrapBefore;
		}

		foreach($array as $index => $item) {
			if( is_string($item) || is_numeric($item) ) {
				if( $callback !== false ) {
					$item	= call_user_func($callback, $item);
				}

				$array[$index] = $wrapBefore . $item . $wrapAfter;
			} else {
				unset($array[$index]);
				TodoyuLogger::logCore('Item was not quoted because was not string or number: ' . serialize($array[$index]), $item);
			}
		}

		return $array;
	}



	/**
	 * Make sure the variable is an array if it's not, there are two options: get an empty array or make the input the first element of the array
	 *
	 * @param	Mixed		$input
	 * @param	boolean		$convert		Convert to array element or get an empty array
	 * @return	Array
	 */
	public static function assure($input, $convert = false) {
		return is_array($input) ? $input : ($convert ? array($input) : array());
	}



	/**
	 * Get array from JSON string
	 *
	 * @param	string		$jsonInput
	 * @return	Array
	 */
	public static function assureFromJSON($jsonInput) {
		return self::assure(json_decode($jsonInput, true));
	}



	/**
	 * Get array from serialized string
	 *
	 * @param	string		$serializedInput
	 * @return	Array
	 */
	public static function assureFromSerialized($serializedInput) {
		return self::assure(unserialize($serializedInput));
	}



	/**
	 * Merge all sub arrays of the array to a single array
	 *
	 * @param	array		$array
	 * @return	Array
	 */
	public static function mergeSubArrays(array $array) {
		if( sizeof($array) === 0 ) {
			return array();
		} elseif( sizeof($array) === 1 ) {
			return array_shift($array);
		} else {
			return call_user_func_array('array_merge', $array);
		}
	}



	/**
	 * Merge multiple arrays and return a unique array
	 * Combination of array_merge and array_unique
	 *
	 * @return	Array
	 */
	public static function mergeUnique(/*arrays*/) {
		$funcArgs	= func_get_args();
		$merged		= call_user_func_array('array_merge', $funcArgs);

		return array_values(array_unique($merged));
	}



	/**
	 * Merge data from $fallbackData into $baseData if not set or empty
	 *
	 * @param	array		$baseData
	 * @param	array		$fallbackData
	 * @return	Array
	 */
	public static function mergeEmptyFields(array $baseData, array $fallbackData) {
		foreach($fallbackData as $key => $value) {
			if( !isset($baseData[$key]) || empty($baseData[$key]) ) {
				$baseData[$key] = $fallbackData[$key];
			}
		}

		return $baseData;
	}



	/**
	 * Merges any number of arrays / parameters recursively, replacing
	 * entries with string keys with values from latter arrays.
	 * If the entry or the next value to be assigned is an array, then it
	 * automatically treats both arguments as an array.
	 * Numeric entries are appended, not replaced, but only if they are
	 * unique
	 *
	 * @see		http://www.php.net/manual/en/function.array-merge-recursive.php
	 * @param	array	$a1
	 * @param	array	$a2
	 * @return	Array
	 */
	public static function mergeRecursive(array $a1, array $a2 /* $a3, $a4, ...*/) {
		$arrays	= func_get_args();
		$base	= array_shift($arrays);

		if( !is_array($base) ) {
			$base = empty($base) ? array() : array($base);
		}

		foreach( $arrays as $append ) {
			if( !is_array($append) ) {
				$append = array($append);
			}
			foreach( $append as $key => $value ) {
				if( !array_key_exists($key, $base) and !is_numeric($key) ) {
					$base[$key] = $append[$key];
					continue;
				}
				if( is_array($value) or is_array($base[$key]) ) {
					$base[$key] = self::mergeRecursive($base[$key], $append[$key]);
				} else if( is_numeric($key) ) {
					if( !in_array($value, $base) ) $base[] = $value;
				} else {
					$base[$key] = $value;
				}
			}
		}

		return $base;
	}



	/**
	 * Merge two arrays. Works also if one of the parameters is not an array (it's ignored)
	 *
	 * @param	Mixed	$arrayA
	 * @param	Mixed	$arrayB
	 * @return	Array
	 */
	public static function merge($arrayA, $arrayB) {
		return array_merge(self::assure($arrayA), self::assure($arrayB));
	}



	/**
	 * Flatten array: flattens multi-dim arrays (destroys keys)
	 *
	 * @param	array	$array
	 * @return	Array
	 */
	public static function flatten(array $array){
		$flattened = array();

		foreach($array as $value) {
			if( is_array($value) ) {
				$flattened = array_merge($flattened, self::flatten($value));
			} else {
				array_push($flattened, $value);
			}
		}

		return $flattened;
	}



	/**
	 * Remove array entries by their value
	 *
	 * @param	array		$array
	 * @param	array		$valuesToRemove
	 * @param	boolean		$reIndex
	 * @return	Array
	 */
	public static function removeByValue(array $array, array $valuesToRemove, $reIndex = true) {
		$array = array_diff($array, $valuesToRemove);

		if( $reIndex ) {
			$array = array_values($array);
		}

		return $array;
	}



	/**
	 * Remove duplicate entries in given field of array
	 *
	 * @param	array	$array
	 * @param	string	$fieldName
	 * @return	Array
	 */
	public static function removeDuplicates(array $array, $fieldName) {
		$values	= array();
		$clean	= array();

			// Iterate all associative sub arrays
		foreach($array as $index => $item) {
			$value	= $item[$fieldName];

			if( ! in_array($value, $values) ) {
				$clean[$index]	= $item;
			}

			$values[] = $value;
		}

		return $clean;
	}



	/**
	 * Explode string
	 * Wrapper for explode to get an empty array for empty strings
	 *
	 * @param	string				$delimiter
	 * @param	string				$string
	 * @param	integer|Boolean		$limit
	 * @return	Array
	 */
	public static function explode($delimiter, $string, $limit = false) {
		if( is_array($string) ) {
			return $string;
		}
		$string	= trim($string);

		if( $string === '' ) {
			return array();
		} else {
				// Explode with or without limit (there is no 'not defined' value, so both calls are required)
			return $limit ? explode($delimiter, $string, $limit) : explode($delimiter, $string);
		}
	}



	/**
	 * Explode a list of integers
	 *
	 * @param	string				$delimiter			Character to split the list
	 * @param	string				$string				The list
	 * @param	boolean				$onlyPositive		Set negative values zero
	 * @param	boolean				$removeZeros		Remove all zero values
	 * @param	integer|Boolean		$limit				Explode to maximum $limit parts (as in explode())
	 * @return	integer[]
	 */
	public static function intExplode($delimiter, $string, $onlyPositive = false, $removeZeros = false, $limit = false) {
		return self::intval(self::explode($delimiter, $string, $limit), $onlyPositive, $removeZeros);
	}



	/**
	 * Explode a list and remove whitespace around the values
	 *
	 * @param	string				$delimiter				Character to split the list
	 * @param	string				$string					The list
	 * @param	boolean				$removeEmptyValues		Remove values which are empty after trim()
	 * @param	integer|Boolean		$limit					Explode to maximum $limit parts (as in explode())
	 * @return	Array
	 */
	public static function trimExplode($delimiter, $string, $removeEmptyValues = false, $limit = false) {
		$parts	= self::explode($delimiter, $string, $limit);
		$array	= array();

		foreach($parts as $value) {
			$value = trim($value);

			if( $removeEmptyValues && $value === '' ) {
				continue;
			}

			$array[] = $value;
		}

		return $array;
	}



	/**
	 * Implode array
	 * Save fallback if not an array
	 *
	 * @param	string		$glue
	 * @param	Mixed		$array
	 * @return	string
	 */
	public static function implode($glue, $array) {
		return is_array($array) ? implode($glue, $array) : trim($array);
	}



	/**
	 * Implode trimmed values
	 * Combination of foreach, trim and implode
	 *
	 * @param	string		$glue
	 * @param	array		$array
	 * @return	string
	 */
	public static function trimImplode($glue, array $array) {
		$array = self::trim($array);

		return implode($glue, $array);
	}



	/**
	 * Trim all elements of an array. The elements have to be strings
	 *
	 * @param	array		$array
	 * @param	boolean		$removeEmpty		Remove empty elements
	 * @return	Array
	 */
	public static function trim(array $array, $removeEmpty = false) {
		$new	= array();
		foreach($array as $value) {
			$value	= trim($value);
			if( !$removeEmpty || $value !== '' ) {
				$new[] = $value;
			}
		}

		return $new;
	}



	/**
	 * Use a field value in an array as index of the array
	 *
	 * @param	array		$array
	 * @param	string		$fieldName
	 * @return	Array
	 */
	public static function useFieldAsIndex(array $array, $fieldName) {
		$new = array();

		foreach($array as $index => $item) {
			$item['_oldIndex']		= $index;
			$new[$item[$fieldName]]	= $item;
		}

		return $new;
	}



	/**
	 * Apply htmlspecialchars() to all elements recursively
	 *
	 * @param	array		$array
	 * @return	Array
	 */
	public static function htmlspecialchars(array $array) {
		foreach($array as $key => $value) {
			if( is_array($value) ) {
				$array[$key] = self::htmlspecialchars($value);
			} elseif( is_string($value) ) {
				$array[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
			} else {
				// Do nothing
				//$array[$key] = $value;
			}
		}

		return $array;
	}



	/**
	 * Get items of the left array which are not in the right one
	 * Difference to array_diff. Unique elements from the right array are ignored
	 *
	 * @param	array	$left
	 * @param	array	$right
	 * @return	Array
	 */
	public static function diffLeft(array $left, array $right) {
		$new = array();

		foreach($left as $leftItem) {
			foreach($right as $rightItem) {
				if( $leftItem == $rightItem ) {
					continue 2;
				}
			}
			$new[] = $leftItem;
		}

		return $new;
	}



	/**
	 * Create an array with the keys from the given array
	 * Set value to value parameter
	 *
	 * @param	array	$keys
	 * @param	Mixed	$value
	 * @return	Array
	 */
	public static function createMap(array $keys, $value = 0) {
		$map	= array();

		foreach($keys as $key) {
			$map[$key] = $value;
		}

		return $map;
	}



	/**
	 * Group an array by a field
	 *
	 * @param	array	$elements
	 * @param	string	$field
	 * @param	string	$fallbackGroup
	 * @return	Array
	 */
	public static function groupByField(array $elements, $field, $fallbackGroup = 'noGroup') {
		$grouped = array();

		foreach($elements as $key => $element) {
			$group = isset($element[$field]) ? $element[$field] : $fallbackGroup;
			$grouped[$group][$key] = $element;
		}

		return $grouped;
	}



	/**
	 * Prepend a custom please select label to a option list
	 * Use this, if you don't want to use the default "Please select" of the select element
	 * The default has to be disabled with <noPleaseSelect />
	 *
	 * @param	array			$options
	 * @param	string			$pleaseSelectLabel
	 * @param	string|Integer	$pleaseSelectValue
	 * @param	boolean			$addSeparator
	 * @return	Array
	 */
	public static function prependSelectOption(array $options, $pleaseSelectLabel = 'core.form.select.pleaseSelect', $pleaseSelectValue = 0, $addSeparator = true) {
		$select		= array(
			'value'		=> $pleaseSelectValue,
			'label'		=> $pleaseSelectLabel
		);
		$separator	= array(
			'value'		=> 0,
			'label'		=> '---------------------------',
			'disabled'	=> true
		);

		if( $addSeparator ) {
			array_unshift($options, $separator);
		}

		array_unshift($options, $select);

		return $options;
	}



	/**
	 * Get items which are allwed for the current user
	 * Check if item has a field which contains a right to check.
	 * No right = allow without check
	 *
	 * @param	array		$items
	 * @param	string		$rightsField		Custom right field
	 * @return	Array
	 */
	public static function getAllowedItems(array $items, $rightsField = 'require') {
		$filtered	= array();

		foreach($items as $key => $item) {
			if( isset($item[$rightsField]) ) {
				list($extKey, $right) = explode('.', $item[$rightsField], 2);
				if( !Todoyu::allowed($extKey, $right) ) {
					continue;
				}
			}

			$filtered[$key] = $item;
		}

		return $filtered;
	}



	/**
	 * Calculate average (optional of a sub element)
	 *
	 * @param	array				$data
	 * @param	string|Null			$subKey
	 * @param	boolean|Integer		$round
	 * @return	Float|Integer
	 */
	public static function average(array $data, $subKey = null, $round = false) {
		$average	= 0;

		if( $subKey ) {
			$data	= self::getColumn($data, $subKey);
		}

		if( sizeof($data) ) {
			$average = array_sum($data) / sizeof($data);
		}

		if( $round !== false ) {
			$average = round($average, $round);
		}

		return $average;
	}

}

?>