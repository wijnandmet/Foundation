<?php 
namespace Libraries\Frame;

use reflectionMethod;
use reflectionClass;

use Libraries\Types\String;


/**
* -----------------------------------------------------------------------------
* Container Library
* -----------------------------------------------------------------------------
* @author      Wijnand de Ridder
* @package     ALF
*
* @copyright   (c) 2015 Wijnand de Ridder
* @license     MIT
* -----------------------------------------------------------------------------
*
* About Container-library
*
* This class gives the tools to create a typehinting-autoload functionality. 
*
* Dependencies: String-Library
*/
class Container {

	/**
	 * Create a IoC-container, that is: load a class with typehinting-autoload functionality
	 *
	 * @param string $concrete the class you want to load
	 * @param string $method   the method you want to load
	 * @param array  $args     the arguments for this method
	 * @param array  $mainargs the arguments for submethods
	 * 
	 * @return mixed result object or value of the object
	 */
	public static function init($concrete,$method = null,$args = [],$mainargs = []) {
		if ($method !== null) {
			// heeft een method
			$methodReflection = new reflectionMethod($concrete,$method);
			$parameters = [];
			$methodArguments = $methodReflection->getParameters();
			$i = 0;
			if (!empty($methodArguments)) {
				$nargs = [];
				foreach ($args AS $k=>$v) {
					if (!is_integer($k)) {
						$nargs[$k] = $v;
						unset($args[$k]);
					}
				}
				$args = array_values($args);
				foreach ($methodArguments AS $k=>$v) {
					$class = $v->getClass();
					if (!is_null($class)) {
						$parameters[] = self::init($class->name,null,[],$mainargs);
					} elseif (isSet($nargs[$v->name])) {
						$parameters[] = $nargs[$v->name];
					} else {
						$parameters[] = @$args[$i];
						++$i;
					}
				}
			}
			if (!empty($parameters)) {
				$reflection = new reflectionClass($concrete);
				return $methodReflection->invokeArgs($reflection->newInstanceWithoutConstructor(),$parameters);
			} else {
				return $methodReflection->invoke(self::init($concrete));
			}
		} else {
			// gebruik construct (als die er is)
			$reflection = new reflectionClass($concrete);
			$constructor = $reflection->getConstructor();
			if (is_null($constructor)) {
				if (String::like($concrete,'Application\Validation\%')) {
					$o = new $concrete;
					$r = $o->execute($o->_rules);
					return $o;
				} elseif (String::like($concrete,'%\\%') === false) {
					return new $concrete($args['id']);
				} else {
					$obj = new $concrete;
					return $obj;
				}
			} else {
				$parameters = [];
				$methodArguments = $constructor->getParameters();
				if (!empty($methodArguments)) {
					foreach ($methodArguments AS $k=>$v) {
						$class = $v->getClass();
						if (!is_null($class)) {
							$parameters[] = self::init($class->name,null,[]);
						} elseif (isSet($args[$v->name])) {
							$parameters[] = $args[$v->name];
						}
					}
				}
				if (!empty($parameters)) {
					return $constructor->invokeArgs($reflection->newInstanceWithoutConstructor(),$parameters);
				} else {
					if (String::like($concrete,'Application\Validation\%')) {
						$o = new $concrete;
						$r = $o->execute($o->_rules);
						return $o;
					} elseif (String::like($concrete,'%\\%') === false && !empty($mainargs['id'])) {
						return new $concrete($mainargs['id']);
					} else {
						return new $concrete;
					}
				}
			}
		}
	}
}
?>