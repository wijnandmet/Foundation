<?php 
namespace Libraries\Frame;

use Libraries\Types\String;
use Libraries\Frame\Request;

/**
* -----------------------------------------------------------------------------
* Request Validation
* -----------------------------------------------------------------------------
* @author      Wijnand de Ridder
* @package     ALF
*
* @copyright   (c) 2015 Wijnand de Ridder
* @license     MIT
* -----------------------------------------------------------------------------
*
* About Validation Library
*
* The base validation-class that is used by specific validation-classes.
*
* Dependencies: String Library, Request Library
*/
Class Validation extends Request {

	/**
	 * Validation-errors
	 *
	 * @var array
	 */
	private $_errors = [];

	/**
	 * Do you want to return the errors as a var OR do you want to throw 
	 * a 'big' error
	 *
	 * @var boolean
	 */
	public $_friendly = false;


	/**
	 * Execute the validations on all the fields that are in the validation
	 * class
	 *
	 * @param array  $rules  the rules for the validation
	 * @param array  $input  the post inputs
	 *
	 * @return object 	the validation object
	 */
	public function execute($rules = null,$input = []) {
		if ($rules === null) {
			$rules = $this->_rules;
		}

		if (empty($input)) {
			$input = $_POST;
		}
		
		foreach ($rules AS $k=>$v) {
			$e = String::split('|',$v);
			foreach ($e AS $k2 => $v2) {
				$oldv2 = null;
				if (String::like($v2,'%:%')) {
					$oldv2 = $v2;
					$e2 = String::split(':',$v2);
					$k2 = $e2[0];
					$v2 = $e2[1];
				}

				if (Filter::isInteger($k2)) {
					if (in_array($v2, ['required','integer','boolean','float','date','email'])) {
						if (!self::$v2(@$input[$k])) {
							$this->_errors[] = $this->makeError($k,$v2);
							break;
						}
					} else {
						$this->_errors[] = $this->makeError($k,$v2);
					}
				} else {
					if (in_array($k2,['max','min'])) {
						if (!self::$k2(@$input[$k],$v2)) {
							$this->_errors[] = $this->makeError($k,$oldv2);
							break;
						}
					} else {
						$this->_errors[] = $this->makeError($k,$oldv2);
					}
				}
			}
		}

		if (!empty($this->_errors)) {
			if ($this->_friendly === true) {
				$this->error = true;
			} else {
				abort(404,$this->errors[0]);
			}
		}
		return $this;
	}

	public function fields() {
		if (empty($this->_rules)) {
			throw new ExceptionALF('No form-rules found');
		}
		return array_keys($this->_rules);
	}

	/**
	 * Make an errorstring
	 *
	 * @param string $name  the name of the column
	 * @param value $value  the given value
	 *
	 * @return string  the errorstring
	 */
	public function makeError($name,$value) {
		$column = $value;
		$num = null;
		if (String::like($column,'%:%')) {
			$e = String::split(':',$column);
			$column = $e[0];
			$num = $e[1];
		}
		if (App::hasLanguage($name)) {
			$name = App::language($name);
		}
		return App::language('validation.error.' . $column,['name' => String::lower($name),'num' => $num]);
	}

	/**
	 * Get the errors
	 *
	 * @return array the errors
	 */
	public function errors() {
		return $this->_errors;
	}

	/**
	 * Check: required
	 *
	 * @param string $v if this is filled, then it is required
	 *
	 * @return boolean  if it suffice
	 */
	public static function required($v) {
		return ($v===null || $v===''?false:true);
	}

	/**
	 * Check: min:{num}
	 *
	 * @param string $string the string that must be checke
	 * @param string $length what is the length that is required
	 *
	 * @return boolean  if it suffice
	 */
	public static function min($string,$length) {
		return (String::length($string)>=$length?true:false);
	}

	/**
	 * Check: max:{num}
	 *
	 * @param string $string the string that must be checke
	 * @param string $length what is the max length
	 *
	 * @return boolean  if it is suffice
	 */
	public static function max($string,$length) {
		return (String::length($string)<=$length?true:false);
	}

	/**
	 * Check: integer
	 *
	 * @param string $string the string that must be checked
	 *
	 * @return boolean  if it suffice
	 */
	public static function integer($string) {
		return (Filter::isNumeric($string)?true:false);
	}

	/**
	 * Check: boolean
	 *
	 * @param string $string the string that must be checked
	 *
	 * @return boolean  if it suffice
	 */
	public static function boolean($string) {
		return (Filter::isBool($string)?true:false);
	}

	/**
	 * Check: float
	 *
	 * @param string $string the string that must be checked
	 *
	 * @return boolean  if it suffice
	 */
	public static function float($string) {
		return (Filter::isFloat($string)?true:false);
	}

	/**
	 * Check: date
	 *
	 * @param string $string the string that must be checked
	 *
	 * @return boolean  if it suffice
	 */
	public static function date($string) {
		return (Filter::isDate($string)?true:false);
	}

	/**
	 * Check: email
	 *
	 * @param string $string the string that must be checked
	 *
	 * @return boolean  if it suffice
	 */
	public static function email($string) {
		return (Filter::isEmail($string)?true:false);
	}
}
?>