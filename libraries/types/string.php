<?php
namespace Libraries\Types;

/**
* -----------------------------------------------------------------------------
* String Library
* -----------------------------------------------------------------------------
* @author      Wijnand de Ridder
* @package     ALF
*
* @copyright   (c) 2015 Wijnand de Ridder
* @license     MIT
* -----------------------------------------------------------------------------
*
* About String Library
*
* This is a Library with all kind of string-methods that are handy for your
* application.
*/
Class String {

	/**
	 * Search inside a string
	 *
	 * @param String $like   	for what do you want to search (with % as wildcard)
	 * @param String $string 	the string in which you want to search
	 *
	 * @return boolean 	if exists returns true
	 */
	public static function like($like,$string) {
		$left = substr($like,0,1);
		$right = substr($like,static::length($like)-1,1);
		$like = static::trim($like,'%');
		if ($left == '%' && $right == '%' && strpos($string,$like) !== false) {
			return true;
		} elseif ($left == '%' && $right != '' && substr($string,static::length($string)-static::length($like),static::length($like)) == $like) {
			return true;
		} elseif ($right == '%' && $left != '' && strpos($string,$like) === 0) {
			return true;
		}
		return false;
	}

	/**
	 * Clear space-charachters around the string
	 *
	 * @param String $string the string from which you want to clear the space-characters
	 * @param String $extra  what are the extra characters you want to clear around the string
	 *
	 * @return String the cleared string
	 */
	public static function trim($string,$extra = null) {
		return trim($string,$extra);
	}


	/**
	 * Make string to slug/url-part
	 *
	 * @param String $string the string that must become an slug/url-part
	 *
	 * @return String  the slug/url-part
	 */
	public static function slug($string) {
		$url = self::lower($string);
		$url = preg_replace("/[^a-zA-Z0-9]+/i","-",$url);
		$url = preg_replace("/[--]+/","-",$url);
		$url = self::trim($url,'-');
		return $url;
	}

	/**
	 * The length of a string
	 *
	 * @param String $value the string from which you want to know the length
	 *
	 * @return Integer the length of the string
	 */
	public static function length($value) {
		return (MB_STRING) ? mb_strlen($value) : strlen($value);
	}

	/**
	 * Convert a string to lowercase
	 *
	 * @param String $value the string you want to get in lowercase
	 *
	 * @return String the string in lowercase
	 */
	public static function lower($value) {
		return (MB_STRING) ? mb_strtolower($value) : strtolower($value);
	}

	/**
	 * Convert a string to uppercase
	 *
	 * @param String $value the string you want to get in uppercase
	 *
	 * @return String the string in uppercase
	 */
	public static function upper($value) {
		return (MB_STRING) ? mb_strtoupper($value) : strtoupper($value);
	}

	/**
	 * Convert a string to have the first letter to uppercase
	 *
	 * @param String $value the string you want to have the first letter in uppercase
	 *
	 * @return String the string with the first letter to uppercase
	 */
	public static function firstUppercase($value) {
		return ucfirst($value);
	}

	/**
	 * Limit the string to a certain length
	 *
	 * @param String  $value the string
	 * @param integer $limit the maximum length
	 * @param string  $end   if the string is longer, what to place in the end
	 *
	 * @return String  the new limited string
	 */
	public static function limit($value, $limit = 999, $end = '...') {
		if (static::length($value) <= $limit) return $value;
		if (MB_STRING) {
			return mb_substr($value, 0, $limit) . $end;
		}
		return substr($value, 0, $limit) . $end;
	}

	/**
	 * Limit the string to a certain amount of words
	 *
	 * @param String  $value the string
	 * @param integer $words maximum number of words
	 * @param string  $end   if the string is longer, what to place in the end
	 *
	 * @return String  the limited string
	 */
	public static function limitWords($value, $words = 100, $end = '...') {
		if (trim($value) == '') return '';
		preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);
		if (static::length($value) == static::length($matches[0])) {
			$end = '';
		}
		return rtrim($matches[0]) . $end;
	}

	/**
	 * Split the string after a certain number
	 *
	 * @param String  $value  the string
	 * @param integer $offset where to start in the string
	 * @param integer $limit  maximum length after the offset
	 *
	 * @return String  the new string
	 */
	public static function substr($value, $offset = 0, $limit = 999) {
		if (MB_STRING) {
			return mb_substr($value, $offset, $limit);
		}
		return substr($value, $offset, $limit);
	}

	/**
	 * Get the segments of an url
	 *
	 * @param String $value the string
	 *
	 * @return Array the segments
	 */
	public static function segments($value) {
		return array_diff(String::split('/', static::trim($value, '/')), array(''));
	}

	/**
	 * Check if this string matching the pattern
	 *
	 * @param String $pattern the pattern to check
	 * @param String $value   the string
	 *
	 * @return boolean does it match?
	 */
	public static function hasPattern($pattern, $value) {
		return preg_match($pattern, $value);
	}

	/**
	 * Replace a part of this string
	 *
	 * @param String $a      what to replace
	 * @param String $b      with what to replace
	 * @param String $string the string
	 *
	 * @return String the converted string
	 */
	public static function replace($a,$b,$string) {
		return str_replace($a,$b,$string);
	}

	/**
	 * Replace a part of the string with regex pattern
	 *
	 * @param String $p      the pattern
	 * @param String $r      with what to replace it
	 * @param String $string the string
	 *
	 * @return String the converted string
	 */
	public static function preg_replace($p,$r,$string) {
		return preg_replace($p,$r,$string);
	}

	/**
	 * Split a string to an array
	 *
	 * @param String $s      delimeter to split with
	 * @param String $string the string
	 *
	 * @return Array the result array
	 */
	public static function split($s,$string) {
		return explode($s,$string);
	}

	/**
	 * Split a string to an array, but let the delimeter be a pattern
	 *
	 * @param String  $pattern          the pattern
	 * @param String  $string           the string
	 * @param Boolean $nonempty 		not the empty results
	 *
	 * @return Array  the result array
	 */
	public static function split_pattern($pattern,$string,$nonempty = false) {
		if ($splitwithsplitter == true) {
			return preg_split($pattern, $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		} else {
			return preg_split($pattern, $string, -1);
		}
	}

	/**
	 * Unsplit an array to a string
	 *
	 * @param String $s      the delimited
	 * @param Array $array  the array to unsplit
	 *
	 * @return String the unsplitted array
	 */
	public static function unsplit($s,$array) {
		return implode($s,$array);
	}

	/**
	 * Remove certain tags
	 *
	 * @param String $string the string
	 * @param String $strip  the tags you want to keep
	 *
	 * @return String the new string without certain tags
	 */
	public static function striptags($string,$strip) {
		return strip_tags($string,$strip);
	}
}
?>