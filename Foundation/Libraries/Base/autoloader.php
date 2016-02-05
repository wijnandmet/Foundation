<?php
namespace Libraries\Frame;


/**
* -----------------------------------------------------------------------------
* Autoloader Library
* -----------------------------------------------------------------------------
* @author      Wijnand de Ridder
* @package     ALF
*
* @copyright   (c) 2015 Wijnand de Ridder
* @license     MIT
* -----------------------------------------------------------------------------
*
* About Autoloader-library
*
* This class includes the right files when a class is called.
*/

Class Autoloader {

	public static $alias = array();
	public static $namespaces = array();
	

	/**
	 * Execute method by __autoload or other autoload-functionalities
	 *
	 * @param string $class the classname (with namespaces if it is given)
	 *
	 * @return voic
	 */
	public static function load($class) {
		if (class_exists($class)) {
			return true;
		}
		$class = strtolower($class);
		if (!empty(static::$alias[$class])) {
			$class = static::$alias[$class];
		}
		$class2 = str_replace('\\',DS,$class);
		//echo R . 'vendors' . DS . $class2 . '.php<br />';
		if (file_exists(R . $class2 . '.php')) {
			include_once(R . $class2 . '.php');
		} elseif (\endsWith($class2,'controller') && file_exists(C . $class2 . '.php')) {
			include_once(C . $class2 . '.php');
		} elseif (\startsWith($class2,'exception') && file_exists(E . $class2 . '.php')) {
			include_once(E . $class2 . '.php');
		} elseif (file_exists(M . $class2 . '.php')) {
			include_once(M . $class2 . '.php');
		} elseif (file_exists(R . 'vendors' . DS . $class2 . '.php')) {
			include_once(R . 'vendors' . DS . $class2 . '.php');
		} else {
			$namespaces = static::$namespaces;
			asort($namespaces);
			$namespaces = array_reverse($namespaces);
			foreach ($namespaces AS $alias=>$dir) {
				$dir = str_replace('\\',DS,$dir);
				$alias = str_replace('\\',DS,$alias);
				if (strpos($class2, $alias . DS) === 0) {
					$file = substr($class2,strlen($alias . DS),strlen($class2));
					if (file_exists($dir . DS . $file . '.php')) {
						include_once($dir . DS . $file . '.php');
						return;
					}
				}
			}
			if (DEBUG == true) {
				echo 'Autoload | <b>' . $class . '</b> niet gevonden.';
			} else {
				echo 'Autoload | <b>' . $class . '</b> niet gevonden.';
				exit();
			}
		}
	}


	/**
	 * Include files that can be autoloaded
	 *
	 * @param mixed $files one or multiple files that must be included
	 *
	 * @return void
	 */
	public static function incl($files) {
		if (!is_array($files)) {
			$files = array($files);
		}
		foreach ($files AS $file) {
			$nfile = R . str_replace('.',DS,$file) . '.php';
			if (file_exists($nfile)) {
				include_once($nfile);
			} elseif (file_exists('..' . DS . $nfile)) {
				include_once('..' . DS . $nfile);
			} else {
				if (DEBUG == true) {
					echo '<strong>Autoload</strong> | file <u>' . $nfile . '</u> couldn\'t be included.';
					exit();
				} else {
					exit();
				}
			}
		}
	}


	/**
	 * Create one or multiple namespaces
	 *
	 * @param array $namespaces the namespaces
	 *
	 * @return void
	 */
	public static function namespaces($namespaces) {
		if (!is_array($namespaces)) {
			$namespaces = array($namespaces);
		}
		self::$namespaces = array_merge(self::$namespaces,$namespaces);
	}

	/**
	 * Create an alias
	 *
	 * @param mixed $alias the alias you want to create
	 *
	 * @return void
	 */
	public static function create($alias) {
		if (!is_array($alias)) {
			$alias = array($alias => $alias);
		}
		if (!empty($alias)) {
			foreach ($alias AS $k=>$v) {
				self::$alias[$k] = $v;
			}
		}
	}
}
?>