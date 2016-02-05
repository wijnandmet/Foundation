<?php
namespace Libraries\Frame;

/**
* -----------------------------------------------------------------------------
* Filter Library
* -----------------------------------------------------------------------------
* @author      Wijnand de Ridder
* @package     ALF
*
* @copyright   (c) 2015 Wijnand de Ridder
* @license     MIT
* -----------------------------------------------------------------------------
*
* About Filter-library
*
* This Library can be used to check all kind of types for validation.
*/
Class Filter {

    /**
     * Check if a value is an email
     *
     * @param string $string emailaddress
     *
     * @return boolean is this value a real emailadres
     */
	public static function isEmail($string) {
        if (preg_match('/^[^\W][a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}$/',$string)) {
            return true;
        }
        return false;
    }

    /**
     * Check if a value is a date
     *
     * @param string $string date
     * @param bool   $time   is there also a time involved
     *
     * @return boolean is this value a real date
     */
    public static function isDate($val,$time = false) {
        if (strtotime($val) !== false) {
            if ($time === true && $val == date("Y-m-d H:i:s",strtotime($val))) {
                return true;
            } elseif ($time === false && $val == date("Y-m-d",strtotime($val))) {
                return true;
            } elseif ($time === true && $val == date("Y-m-d",strtotime($val))) {
                return true;
            } elseif ($time === false && $val == date("d-m-Y",strtotime($val))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a value is a float
     *
     * @param mixed $string the value
     *
     * @return boolean is this value a real float
     */
    public static function isFloat($string) {
        return is_float($string);
    }

    /**
     * Convert a value to a float
     *
     * @param mixed $string the value
     *
     * @return the value as a float-value
     */
    public static function toFloat($string) {
        return preg_replace("/[^0-9\.]+/","",$string);
    }

    /**
     * Check if a value is a number
     *
     * @param mixed $string the value
     *
     * @return boolean is this value a real number
     */
    public static function isNumeric($string) {
        return is_numeric($string);
    }

    /**
     * Convert a value to a number
     *
     * @param mixed $string the value
     *
     * @return the value as a number
     */
    public static function toNumeric($int) {
        return (int)self::toNumeric($int);
    }

    /**
     * Check if a value is a integer
     *
     * @param mixed $string the value
     *
     * @return boolean is this value a real integer
     */
    public static function isInteger($string) {
        return is_int($string);
    }

    /**
     * Convert a value to a integer
     *
     * @param mixed $string the value
     *
     * @return the value as a integer-value
     */
    public static function toInteger($string) {
        return preg_replace("/[^0-9]+/","",$string);
    }
    
    /**
     * Check if a value is a boolean
     *
     * @param mixed $string the value
     *
     * @return boolean is this value a real boolean
     */
    public static function isBool($string) {
        return is_bool($string);
    }

    /**
     * Convert a value to a alfa
     *
     * @param mixed $string the value
     *
     * @return the value as a alfa-value
     */
    public static function toAlfa($string) {
        return preg_replace("/[^a-z]+/","",strtolower($string));
    }

    /**
     * Convert a value to a alfanumeric
     *
     * @param mixed $string the value
     *
     * @return the value as a alfanumeric-value
     */
    public static function toAlfaNumeric($string) {
        return preg_replace("/[^a-z0-9]+/","",strtolower($string));
    }
}