<?php 
namespace Libraries\Frame;

use Libraries\Types\String;

/**
* -----------------------------------------------------------------------------
* APP Library
* -----------------------------------------------------------------------------
* @author      Wijnand de Ridder
* @package     ALF
*
* @copyright   (c) 2015 Wijnand de Ridder
* @license     MIT
* -----------------------------------------------------------------------------
*
* About APP-library
*
* This class is the base-class for the application. 
*
* Dependencies: Helper-Library and String-Library
*/

class App {

	/**
	 * Vars that need to be remembered in your application
	 *
	 * @var array
	 */
	protected static $_vars = [];

	/**
	 * Vars that are available in the main view
	 *
	 * @var array
	 */
	protected static $_view = ['title' => ''];

	/**
	 * Vars with language content
	 *
	 * @var array
	 */
	protected static $_language = [];

	/**
	 * Var with last updated time, so you can check how long a action/job takes
	 *
	 * @var null
	 */
	protected static $_time = null;


	/**
	 * Makes and IoC-container, that is: gives more functionality to a class with typehinting
	 *
	 * @param string $class    name of the class
	 * @param string $method   name of the method
	 * @param array  $args     arguments for this specify method
	 * @param array  $main     arguments for the sub-methods
	 *
	 * @return mixed the result of the selected/created object
	 */
	public static function container($class,$method = null,$args = [],$main = []) {
		return Container::init($class,$method,$args,$main);
	}

	/**
	 * Add a array to the language-array
	 *
	 * @param array $lang the new array that has to be added
	 */
	public static function setLanguage($lang) {
		self::$_language = self::$_language+$lang;
		return self::$_language;
	}

	/**
	 * Does the key exists in the language
	 *
	 * @param string $key  the name of the key
	 *
	 * @return boolean result
	 */
	public static function hasLanguage($key) {
		return isSet(self::$_language[$key]);
	}

	/**
	 * Get the result of an key from the languages-array
	 *
	 * @param string $key  the name of the key
	 * @param array  $args args that can be replaced
	 *
	 * @return string the result value of the key
	 */
	public static function language($key,$args = []) {
		$lang = @self::$_language[$key];
		if (!empty($lang)) {
			if (!empty($args)) {
				foreach ($args AS $name=>$value) {
					$lang = String::replace('{' . $name . '}',$value,$lang);
				}
			}
			return $lang;
		}
		throw new \ExceptionLanguage('Key "' . $key . '" could\'t be found');
	}

	/**
	 * Remember the current time and return it
	 *
	 * @return microtime the current microtime
	 */
	public static function timeUpdate() {
		if (!empty(self::$_time)) {
			return self::$_time;
		}
		return self::$_time = microtime(true);
	}

	/**
	 * Create an new view
	 *
	 * @param string $template the name of the view/template (location)
	 * @param array  $vars     the vars that needs to be in this view
	 *
	 * @return string the result of the view
	 */
	public static function view($template,$vars = []) {
		$vars = array_merge(self::$_view,$vars);
		$view = new View($template,$vars);
		$view->time_start = App::timeUpdate();
		try {
			$str = $view->render();
		} catch (Exception $e) {
			throw new \ExceptionView('View "' . $template . '" couldn\'t be rendered');
		}
		return str_replace("http://beheer.elementa.com/",PROJECT_URL . '/',$str);
	}

	/**
	 * Add a variable to the main view
	 *
	 * @param string $key   the key for this variable
	 * @param mixed $value  the value for this variable
	 *
	 * @return void
	 */
	public static function toView($key,$value) {
		self::$_view[$key] = $value;
	}

	/**
	 * Get a variable from the main view
	 *
	 * @param string $key   the key for this variable
	 *
	 * @return mixed result
	 */
	public static function fromView($key) {
		return self::$_view[$key];
	}

	/**
	 * Get a variable from the main view
	 *
	 * @param string $key   the key for this variable
	 *
	 * @return mixed result
	 */
	public static function inView($key) {
		return isSet(self::$_view[$key]);
	}

	/**
	 * Set a variable to this application
	 *
	 * @param string $key   the key of the variable
	 * @param mixed $value  the value for this variable
	 *
	 * @return void
	 */
	public static function set($key,$value) {
		self::$_vars[$key] = $value;
	}

	/**
	 * Add a breadcrumb to the mainview
	 *
	 * @param string $slug  the slug/segment of url
	 * @param string $title the title
	 *
	 * @return void
	 */
	public static function addBreadcrumb($slug,$title) {
		if (!isSet(self::$_view['breadcrumbs'])) {
			self::$_view['breadcrumbs'] = [];
		}
		self::$_view['breadcrumbs'][] = ['slug' => $slug,'name' => $title];
	}


	/**
	 * Check if a segment is part of the breadcrumbs
	 *
	 * @param string $slug  the slug/segment of url
	 *
	 * @return boolean  this segment is part of the breadcrumbs
	 */
	public static function hasBreadcrumb($slug) {
		if (!isSet(self::$_view['breadcrumbs'])) {
			self::$_view['breadcrumbs'] = [];
		}
		foreach (self::$_view['breadcrumbs'] AS $b) {
			if ($b['slug'] == $slug) {
				return true;
				break;
			}
		}
		return false;
	}

	/**
	 * Set a variable to the next page on this application
	 *
	 * @param string $key   the key of the variable
	 * @param mixed $value  the value for this variable
	 *
	 * @return void
	 */
	public static function setToNext($key,$value) {
		if (empty($_SESSION['next'])) {
			$_SESSION['next'] = [];
		}
		$_SESSION['next'][$key] = $value;
	}

	/**
	 * Get a variable from this application
	 *
	 * @param string $key   the key of the variable
	 *
	 * @return the value for this variable
	 */
	public static function get($key) {
		if (!empty($_SESSION['next']) && !empty($_SESSION['next'][$key])) {
			return $_SESSION['next'][$key]; 
		}
		return @self::$_vars[$key];
	}
}
?>