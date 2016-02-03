<?php
namespace Libraries\Types;

/**
* -----------------------------------------------------------------------------
* Arr Library
* -----------------------------------------------------------------------------
* @author      Wijnand de Ridder
* @package     ALF
*
* @copyright   (c) 2015 Wijnand de Ridder
* @license     MIT
* -----------------------------------------------------------------------------
*
* About Arr Library
*
* This library is a collection of handy array-methods
*/
Class Collection {

	/**
	 * Removes a part of the strings in the array
	 *
	 * @param Array  $array  the haystack array
	 * @param string $var    the string you want to replace (take away)
	 *
	 * @return array return the array which is stripped
	 */
	public static function stripArray(Array $array,$var) {
		return static::replaceInArray($v,$var,'');
	}


	/**
	 * Check if a value has one or more of the given values/needles
	 *
	 * @param String $string the string
	 * @param Mixed $needles  the needle(s) you want to check
	 *
	 * @return boolean 	is one of the values given in de string
	 */
	public static function strposArray($string, $needles) {
	    if (is_array($needles)) {
	        foreach ($needles as $str) {
	            if (is_array($str)) {
	                $pos = static::strpos_array($haystack, $str);
	            } else {
	                $pos = strpos($haystack, $str);
	            }
	            if ($pos !== false) {
	                return $pos;
	            }
	        }
	    } else {
	        return strpos($haystack, $needles);
	    }
	}

	/**
	 * Replace a part of the string in the array
	 *
	 * @param Array  $array the haystack array
	 * @param String $what  the part you want to replace
	 * @param string $for   the part by which you want to replace it
	 *
	 * @return Array the array that has been replaced
	 */
	public static function replaceInArray(Array $array,$what,$for) {
		if (!empty($array)) {
			foreach ($array AS &$v) {
				if (is_array($v)) {
					$v = static::replaceInArray($v);
				} else {
					$v = str_replace($what,$for,$v);
				}
			}
		}
		return $array;
	}

	/**
	 * Get the first value of this array
	 *
	 * @param Array $array The haystack
	 *
	 * @return Mixed 	get the first result
	 */
	public static function first($array) {
		if (!empty($array)) {
			return reset($array);
		}
		return false;
	}
	
	/**
	 * Get the keys of this array
	 *
	 * @param Array $array The haystack
	 *
	 * @return Array  	the keys
	 */
	public static function getKeys($array) {
		return array_keys($array);
	}

	/**
	 * Get the values of this array
	 *
	 * * @param Array $array The haystack
	 *
	 * @return Array  	the values
	 */
	public static function getValues($array) {
		return array_values($array);
	}

	/**
	 * Reverse the order for this array
	 *
	 * @param Array $array The haystack
	 *
	 * @return Array 	the array
	 */
	public static function reverse($array) {
		return array_reverse($array);
	}

	/**
	 * The last value of this array
	 *
	 * @param Array $array The haystack
	 *
	 * @return Mixed 	get the last result
	 */
	public static function last($array) {
		if (empty($array)) {
			return false;
		}
		return end($array);
	}

	/**
	 * Sort the array on its keys
	 *
	 * @param Array $array  The haystack
	 * @param string $way   ASC or DESC
	 *
	 * @return Array  	the result array
	 */
	public static function sort($array,$way = 'ASC') {
		$way = String::upper($way);
		asort($array);
		if ($way == 'DESC') {
			$array = static::reverse($array);
		}
		return $array;
	}
}
?>