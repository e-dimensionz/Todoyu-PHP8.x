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
 * Numeric helper functions
 *
 * @package		Todoyu
 * @subpackage	Core
 */
class TodoyuNumeric {

	/**
	 * Bytes per kilobyte
	 *
	 * @var	Integer
	 */
	const BYTES_KILOBYTE	= 1024;

	/**
	 * Bytes per megabyte
	 *
	 * @var	Integer
	 */
	const BYTES_MEGABYTE	= 1048576;

	/**
	 * Bytes per gigabyte
	 *
	 * @var	Integer
	 */
	const BYTES_GIGABYTE	= 1073741824;



	/**
	 * Make sure an integer is in a range. If the integer is out of range,
	 * set it to one of the boundaries
	 *
	 * @param	integer		$integer
	 * @param	integer		$min
	 * @param	integer		$max
	 * @return	integer
	 */
	public static function intInRange($integer, $min = 0, $max = 2000000000) {
		$integer = (int) $integer;

		if( $integer < $min ) {
			$integer = $min;
		}

		if( $integer > $max ) {
			$integer = $max;
		}

		return $integer;
	}



	/**
	 * Get the positive integer of a value
	 *
	 * @param	string		$value		A string or integer value
	 * @return	integer		Integer equal or greater than 0
	 */
	public static function intPositive($value) {
		$integer = (int) $value;

		if( $integer < 0 ) {
			$integer = 0;
		}

		return $integer;
	}



	/**
	 * Check whether given version is same or newer than version to be compared to
	 *
	 * @param	string		$actualVersion
	 * @param	string		$atLeastVersion
	 * @return	boolean
	 */
	public static function isVersionAtLeast($actualVersion, $atLeastVersion) {
		return version_compare($actualVersion, $atLeastVersion) >= 0;
	}



	/**
	 * Get percent of a value
	 *
	 * @param	Float		$value				Base value
	 * @param	Float		$percent			Percent: 25.0 or 0.25
	 * @param	boolean		$isFraction			Percent value is already a fraction (<0)
	 * @return	Float
	 */
	public static function percent($value, $percent, $isFraction = false) {
		$value		= floatval($value);
		$percent	= floatval($percent);

		if( $isFraction !== true ) {
			$percent = floatval($percent / 100);
		}

		return floatval($percent * $value);
	}



	/**
	 * Get ratio of two values
	 * Supports rounding and percentage value
	 *
	 * @param	Float			$dividend
	 * @param	Float			$divisor
	 * @param	boolean			$asPercent
	 * @param	boolean|Integer	$round
	 * @param	boolean|Mixed	$default
	 * @return	Float|Boolean|Mixed
	 */
	public static function ratio($dividend, $divisor, $asPercent = false, $round = false, $default = false) {
		$dividend	= floatval($dividend);
		$divisor	= floatval($divisor);

		if( $dividend === 0.0 || $divisor === 0.0 ) {
			return $default;
		}

		$ratio	= $dividend/$divisor;

		if( $asPercent ) {
			$ratio *= 100;
		}

		if( $round !== false ) {
			$ratio = round($ratio, $round);
		}

		return $ratio;
	}

}

?>