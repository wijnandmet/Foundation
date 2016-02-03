<?php
namespace Libraries\Frame;

//use \Closure;
use String;

/**
* -----------------------------------------------------------------------------
* Route Library
* -----------------------------------------------------------------------------
* @author      Wijnand de Ridder
* @package     ALF
*
* @copyright   (c) 2015 Wijnand de Ridder
* @license     MIT
* -----------------------------------------------------------------------------
*
* About Route Library
*
* This library is an easy-to-use class that uses phpmailer (that is under this
* class). With this library you can send emails.
*
* Dependencies: String Library
*/
Class Route {

	/**
	 * All the routes divided in get, post and any
	 *
	 * @var array
	 */
	protected static $routes = ['get'=>[],'post'=>[],'any' => []];

	/**
	 * Filters for the routes. A filter is callback-function that is started
	 * before the route is activated
	 *
	 * @var array
	 */
	protected static $filters = [];

	/**
	 * All the groups in which routes are available
	 *
	 * @var array
	 */
	protected static $groups = [];

	/**
	 * Is the 'current route' in a group
	 *
	 * @var boolean
	 */
	protected static $ingroup = false;

	/**
	 * Filters for the routes, diveded in get, post and any
	 *
	 * @var array
	 */
	protected static $filters_to_routes = ['get'=>[],'post'=>[],'any' => []];
	
	/**
	 * The patterns that are available for the routes
	 *
	 * @var array
	 */
	public static $patterns = [
		'(:num)' => '([0-9]+)',
		'(:any)' => '([a-zA-Z0-9\.\-_%=]+)',
		'(:email)' => '([a-zA-Z0-9\.\-_\@]+)',
		'(:segment)' => '([^/]+)',
		'(:all)' => '(.*)',
		'{id}' => '([0-9]+)',
		'{slug}' => '([0-9a-zA-Z-_%]+)',
	];

	/**
	 * The patterns (that are optional) that are available for the routes
	 *
	 * @var array
	 */
	public static $optional_patterns = [
		'/(:num?)' => '(?:/([0-9]+)',
		'/(:any?)' => '(?:/([a-zA-Z0-9\.\-_%=]+)',
		'/(:email?)' => '(?:/([a-zA-Z0-9\.\-_\@]+)',
		'/(:segment?)' => '(?:/([^/]+)',
		'/(:all?)' => '(?:/(.*)',
	];

	/**
	 * The available mime-types for files
	 *
	 * @var array
	 */
	public static $mime_types = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'eot' => 'font/opentype',
        'tiff' => 'font/opentype',
        'tif' => 'font/opentype',
        'ttf' => 'font/opentype',
        'woff' => 'font/opentype',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint',


        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    ];


	/**
	 * Create a file route
	 *
	 * @param string  $key       the route/pattern of a file
	 * @param string  $location  location of the file
	 * @param url     $url       the url of the file
	 * @param boolean $php       if it is a php file, then include the file (and exit)
	 *
	 * @return void
	 */
	public static function file($key,$location,$url,$php = false) {
		$pattern = '#^'.static::wildcards($key).'$#u';
		if (preg_match($pattern, $url, $parameters)) {
			$parameters = array_slice($parameters,1);
			if (!empty($parameters)) {
				$i = 0;
				foreach ($parameters AS $paramter) {
					++$i;
					$location = str_replace('$' . $i,$paramter,$location);
				}
			}
			$location2 = str_replace('/',DS,$location);
			if (!file_exists($location2)) {
				throw new \ExceptionRoute('Couldn\'t found route');
			}
			$exp = explode('.',$location2);
	    	$ext = strtolower(array_pop($exp));
	    	if ($php == true) {
	    		include($location2);
	    		exit();
	    	}
	    	$a = self::$mime_types[$ext];
	    	if ($a) {
	    		header("Content-Type: " . static::$mime_types[$ext]);
	    	}
			echo file_get_contents($location2);
			exit();
		}
	}

    /**
     * Create a get-route
     *
     * @param string $key     the route (pattern)
     * @param mixed $func     callback OR string-container with the method 
     * what to do (or where to go)
     * @param array  $filters apply one or multiple filters
     *
     * @return void
     */
	public static function get($key,$func,$filters = []) {
		$key = ltrim(rtrim(static::$ingroup,'/') . '/' . $key,'/');
		static::$routes['get'][$key] = $func;
		static::$filters_to_routes['get'][$key] = $filters;
	}

	/**
	 * Create a post-route
	 *
	 * @param string $key     the route (pattern)
     * @param mixed $func     callback OR string-container with the method 
     * what to do (or where to go)
     * @param array  $filters apply one or multiple filters
	 *
	 * @return void
	 */
	public static function post($key,$func,$filters = []) {
		$key = ltrim(rtrim(static::$ingroup,'/') . '/' . $key,'/');
		static::$routes['post'][$key] = $func;
		static::$filters_to_routes['post'][$key] = $filters;
	}

	/**
	 * Create a post-route and get-route in one
	 *
	 * @param string $key     the route (pattern)
     * @param mixed $func     callback OR string-container with the method 
     * what to do (or where to go)
     * @param array  $filters apply one or multiple filters
	 *
	 * @return void
	 */
	public static function any($key,$func,$filters = []) {
		$key = ltrim(rtrim(static::$ingroup,'/') . '/' . $key,'/');
		static::$routes['any'][$key] = $func;
		static::$filters_to_routes['any'][$key] = $filters;
	}

	/**
	 * Set a route-group
	 *
	 * @param string $route   	a route for a group
	 * @param function $callb   the callback-function in which the routes get
	 * @param array  $filters 	the filters for this group
	 *
	 * @return void
	 */
	public static function group($route,$callb,$filters = []) {
		static::$groups[$route] = $callb;
		if (!empty($filters)) {
			static::$filters_to_routes['any'][$route] = $filters;
		}
		static::$ingroup = $route;
		$str = call_user_func($callb);
		static::$ingroup = false;
	}

	/**
	 * Set a filter
	 *
	 * @param string $name  	the name of a filter
	 * @param function $callb 	the callback that this filter should have
	 *
	 * @return void
	 */
	public static function filter($name,$callb) {
		static::$filters[$name] = $callb;
	}

	/**
	 * Execute the right route (if it is available)
	 *
	 * @param string $url the current url
	 *
	 * @return string result
	 */
	public static function execute($url) {
		$url = rtrim($url,'/');
		$sort = [];
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$sort['post'] = static::$routes['post'];
			$sort['get'] = static::$routes['get'];
		} else {
			$sort['get'] = static::$routes['get'];
		}
		$sort['any'] = static::$routes['any'];

		// groups
		$groups = static::$groups;
		if (!empty($groups)) {
			foreach ($groups AS $route=>$method) {
				$pattern = '#^'.static::wildcards($route).'#u';
				if (preg_match($pattern, $url, $parameters)) {
					$parameters = array_slice($parameters,1);
					$str = static::before_after('before','any',$route);
					$result = static::getByRoute($sort,$url);
					if ($result !== false) {
						$str .= $result;
						$str .= static::before_after('after','any',$route);
						return $str;
						break;
					}
					$str .= static::before_after('after','any',$route);
				}
			}
		}

		// routes (normal, not groups)
		$result = static::getByRoute($sort,$url);

		if ($result !== false) {
			return $result;
		}

		throw new Exception(App::language('error.unknown-route'));
	}

	/**
	 * Go throught the normal routes
	 *
	 * @param string $sort first the post, then the get, then the any route
	 * @param string $url  the current url
	 *
	 * @return result
	 */
	protected static function getByRoute($sort,$url) {
		$end = false;
		foreach ($sort AS $k=>$s) {
			if (!empty($s)) {
				foreach ($s AS $route=>$method) {
					$pattern = '#^'.static::wildcards(trim($route,'/')).'$#u';

					if (preg_match($pattern, $url, $parameters)) {
						$end = true;
						$parameters = array_slice($parameters,1);
						$str = static::before_after('before',$k,$route);
						
						// parameters name changing if needed
						$i = 0;
						$e = String::split('/',$route);
							foreach ($e AS $part) {
							if (preg_match('#^{(.+)}$#',$part)) {
								$field = String::replace('}','',String::replace('{','',$part));
								$parameters[$field] = $parameters[$i];
								unset($parameters[$i]);
							}
							if (strpos($part, '(') !== false) {
								++$i;
							}
						}

						// execute route
						if (is_string($method)) {
							$e = String::split('::',$method);
							$str .= App::container($e[0],$e[1],$parameters,$parameters);
						} else {
							$str .= call_user_func_array($method,$parameters);
						}
						
						$str .= static::before_after('after',$k,$route);
						return $str;
						break;
					}
					
				}
				exit();
				if ($end === true) {
					break;
				}
			}
		}
	}

	/**
	 * Go through the filters
	 *
	 * @param string $ba    before or after
	 * @param string $k     the key, that is: is it key, post or any?
	 * @param string $route the route itself
	 *
	 * @return result
	 */
	protected static function before_after($ba,$k,$route) {
		$str = '';
		if (!empty(static::$filters_to_routes[$k][$route][$ba])) {
			$before_after = (static::$filters_to_routes[$k][$route][$ba]);
			$ex = explode('|',$before_after);
			foreach ($ex AS $e) {
				if (!empty(static::$filters[$e])) {
					$str .= call_user_func_array(static::$filters[$e],[]);
				} else {
					echo '<strong>Error: filter "' . $bv . '" is niet gevonden.</strong>';
					exit();
				}
			}
		}
		return $str;
	}

	/**
	 * Make the route-patterns in the right regex
	 *
	 * @param string $key the key to be replaced
	 *
	 * @return string the pattern
	 */
	protected static function wildcards($key) {
		list($search, $replace) = [array_keys(static::$optional_patterns), array_values(static::$optional_patterns)];
		$key = str_replace($search, $replace, $key, $count);
		if ($count > 0) {
			$key .= str_repeat(')?', $count);
		}
		return strtr($key, static::$patterns);
	}
}