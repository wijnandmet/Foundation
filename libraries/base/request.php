<?php
namespace Libraries\Frame;

use Libraries\Types\String;
use Libraries\Types\File;
use ExceptionALF;

/**
* -----------------------------------------------------------------------------
* Request Library
* -----------------------------------------------------------------------------
* @author      Wijnand de Ridder
* @package     ALF
*
* @copyright   (c) 2015 Wijnand de Ridder
* @license     MIT
* -----------------------------------------------------------------------------
*
* About Request Library
*
* This library is an easy-to-use class that uses phpmailer (that is under this
* class). With this library you can send emails.
*
* Dependencies: String Library
*/
Class Request {

	/**
	 * The vars that are used in this 
	 *
	 * @var array
	 */
	protected static $_vars = [];


	/**
	 * Magic Method __call(), when a method is called for non-static, return 
	 * the static
	 *
	 * @param string $method    the method that is called for
	 * @param array  $arguments the arguments for the method
	 *
	 * @return mixed the results from the static method
	 */
	public function __call($method,$arguments = []) {
		if (method_exists($this,$method)) {
			return self::$method($arguments);
		}
	}

	/**
	 * Initialize the vars for this class
	 *
	 * @return void
	 */
	public static function init($refresh = false) {
		if (self::$_vars == [] || $refresh === true) {
			
			// make get
			$get = [];
			$url = String::split('?',$_SERVER['REQUEST_URI']);

			if (count($url) === 1) {
				$url = String::split('&',$_SERVER['QUERY_STRING']);
				if (count($url) === 1) {
					$url = '';
				} else {
					unset($url[0]);
					foreach ($url AS $v) {
						$p = String::split('=',$v);
						$get[$p[0]] = $p[1];
					}
				}
			} else {
				unset($url[0]);
				$url = String::unsplit('',$url);
				$url = String::split('&',$url);
				foreach ($url AS $v) {

					$p = String::split('=',$v);
					$get[$p[0]] = $p[1];
				}
			}
			self::$_vars = [
				'querystring' => String::split('&',String::trim($_SERVER['QUERY_STRING'],'/'))[0],
				'redirect' => @$_SERVER['HTTP_REFERER'],
				'url' => DOMEIN . '/' . $_SERVER['QUERY_STRING'],
				'root' => DOMEIN,
				'fullurl' => String::lower((!empty($_SERVER['REQUEST_SCHEME'])?$_SERVER['REQUEST_SCHEME']:'HTTP')) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
				'schema' => (!empty($_SERVER['REQUEST_SCHEME'])?$_SERVER['REQUEST_SCHEME']:'HTTP'),
				'ip' => $_SERVER['REMOTE_ADDR'],
				'post' => $_POST,
				'get' => $get
			];
		}
	}

	/**
	 * Get the querystring
	 *
	 * @return string the querystring
	 */
	public static function querystring() {
		self::init();
		return self::$_vars['querystring'];
	}

	/**
	 * Redirect to a url/page
	 *
	 * @param string $url  the url to which you want to direct
	 * @param bool   $e301 is this redirect a 301 redirect?
	 *
	 * @return void
	 */
	public static function to($url = null,$e301 = false) {
		if ($e301 === true) {
			header("HTTP/1.1 301 Moved Permanently"); 
		}
		if (empty($url)) {
			$url = self::$_vars['redirect'];
			if (empty($url)) {
				$url = PROJECT_URL;
			}
		} else {
			$r = String::strpos_array($url,array('http://','https://','www.'));
			if ($r === false || $r === null || $r > 0) {
				$url = PROJECT_URL . $url;
			}
		}
		if (empty($url)) {
			$url = rtrim(PROJECT_URL,'/') . '/';
		}
		Header("Location:" . $url);
		exit();
	}

	public static function back($arguments = []) {
		if (!empty($arguments)) {
			foreach ($arguments AS $k=>$v) {
				App::setToNext($k,$v);
			}
		}
		
		self::to();
	}

	/**
	 * Get the root URL for the application.
	 *
	 * @return string
	 */
	public static function root() {
		self::init();
		return self::$_vars['root'];
	}

	/**
	 * Get the url
	 *
	 * @return string the url
	 */
	public static function url() {
		self::init();
		return self::$_vars['url'];
	}

	/**
	 * Get the full URL for the request
	 *
	 * @return string
	 */
	public static function fullUrl() {
		self::init();
		return self::$_vars['fullurl'];
	}

	/**
	 * Get all of the segments for the request url
	 *
	 * @return array
	 */
	public static function segments($url = null) {
		self::init();
		return String::segments(($url?$url:self::$_vars['querystring']));
	}

	/**
	 * Check if the segment is part of the url
	 *
	 * @param string $segment the segment you want to check for
	 * @param string $url     the url (if null -> check the current url)
	 *
	 * @return boolean is this segment part of the url
	 */
	public static function hasSegment($segment,$url = null) {
		$segments = (String::segments(($url?$url:self::$_vars['querystring'])));
		return in_array($segment, $segments);
	}

	public static function urlWithoutSegment($segment,$url = null) {
		$segments = (String::segments(($url?$url:self::$_vars['querystring'])));
		if(($key = array_search($segment, $segments)) !== false) {
		    unset($segments[$key]);
		}
		return '/' . String::unsplit('/',$segments);
	}

	/**
	 * Determine if the request is the result of an AJAX call
	 *
	 * @return bool
	 */
	public static function ajax()
	{
		return isAjax();
	}

	/**
	 * Determine if the request is over HTTPS
	 *
	 * @return bool
	 */
	public static function secure() {
		self::init();
		return (self::$_vars['schema'] == 'https'?true:false);
	}

	/**
	 * Returns the client IP address
	 *
	 * @return string
	 */
	public static function ip() {
		self::init();
		return self::$_vars['ip'];
	}

	/**
	 * Get post-input (post)
	 *
	 * @return mixed  the result of the post-input
	 */
	public static function post() {
		self::init();
		return self::$_vars['post'];
	}

	/**
	 * Get get-input (get)
	 *
	 * @return mixed  the result of the get-input
	 */
	public static function get() {
		self::init();
		return self::$_vars['get'];
	}

	/**
	 * Get all inputs (post + get)
	 *
	 * @return Array all inputs
	 */
	public static function all() {
		self::init();
		return self::$_vars['post']+self::$_vars['get'];
	}

	/**
	 * Get input (post or get)
	 *
	 * @return mixed  the result of the input
	 */
	public static function input($key = null, $default = null) {
		$input = self::all();
		if ($key === null) {
			return $input;
		}
		if (self::has($key)) {
			if ($key == '' && $default !== null) {
				return $default;
			}
			return $input[$key];
		}
		if ($default !== null) {
			return $default;
		}
		throw new ExceptionALF("Not all the formelements where found.");
	}

	/**
	 * Get file-input
	 *
	 * @return mixed  the result of the input
	 */
	public static function file($key = null) {
		return File::init($key);
	}
	

	/**
	 * Determine if the request contains a non-empty value for an input item
	 *
	 * @param  string|array  $key
	 * @return bool
	 */
	public static function has($key) {
		$input = self::all();
		if (is_array($key)) {
			foreach ($key AS $k) {
				if (!isSet($input[$k])) {
					return false;
				}
			}
			return true;
		}
		return isSet($input[$key]);
	}

	/**
	 * Get a subset of the items from the input data
	 *
	 * @param  array  $keys
	 * @return array
	 */
	public static function only($keys) {
		$input = self::all();
		$arr = [];
		if (is_array($keys)) {
			foreach ($keys AS $key) {
				$arr[$key] = self::input($key);
			}
		} else {
			$arr[$keys] = self::input($keys);
		}
		return $arr;
	}

	/**
	 * Get all of the input except for a specified array of items
	 *
	 * @param  array  $keys
	 * @return array
	 */
	public static function except($keys)
	{
		$input = self::all();
		if (is_array($keys)) {
			foreach ($keys AS $key) {
				unset($input[$key]);
			}
		} else {
			unset($input[$keys]);
		}
		return $input;
	}

	/**
	 * Retrieve a server variable from the request
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return string|array
	 */
	public static function server($key = null, $default = null) {
		$server = $_SERVER;
		if (isset($server[$key])) {
			if ($key == '' && $default !== null) {
				return $default;
			}
			return $server[$key];
		}
		if ($default !== null) {
			return $default;
		}
		return $server;
	}
}