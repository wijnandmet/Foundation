<?php
namespace Libraries\Frame;

use Libraries\Types\String;

/**
* -----------------------------------------------------------------------------
* HTML Library
* -----------------------------------------------------------------------------
* @author      Wijnand de Ridder
* @package     ALF
*
* @copyright   (c) 2015 Wijnand de Ridder
* @license     MIT
* -----------------------------------------------------------------------------
*
* About HTML Library
*
* This file has tools to be used in views/templates. It let you create html-
* elements and gives you some need functionality.
*
* Dependencies: String-library
*/

class HTML {

	/**
	 * An collection with callbacks used in the bundling of arguments (element)
	 *
	 * @var array
	 */
	public static $_callback_arrays = array();

	/**
	 * And collection with the css/js files
	 *
	 * @var array
	 */
	protected static $files = array('css' => [],'js' => []);


	/**
	 * Create an ajax-page
	 *
	 * @param string $class name (for class="") for this ajax-page
	 *
	 * @return string returns a string with the ajax-div
	 */
	public static function startAjax($class = '') {
		if (isAjax()) {
			ob_end_clean();
		} else {
			return '<div id="ajax-container" class="' . $class . '">';
		}
	}

	/**
	 * Ends an ajax-age
	 *
	 * @return string returns the end of an ajax-div
	 */
	public static function endAjax() {
		if (isAjax()) {
			exit;
		} else {
			return '</div>';
		}
	}

	/**
	* Create an html-element/tag
	*
	* @param string $type   the tag
	* @param array  $args   arguments you want to give to the html-tag
	* @param string $inner  a value or method(string) to execute/return
	* 
	* @return string  the html of the element
	*/
	public static function element($type,$args = null,$inner = null) {
		if (in_array($type,array('br','input','img'))) {
			return '<' . $type . ' ' . self::getArgs($args) . ' />';
		} elseif ($args === null) {
			return '<' . $type . ' />';
		} else {
			if (is_callable($inner) && !is_string($inner)) {
				ob_start();
				$inner();
				$innerHTML = ob_get_clean();
			} else {
				$innerHTML = $inner;
			}
			if ($type != 'span') {
				return '<' . $type . ' ' . self::getArgs($args) . '>' . $innerHTML . '</' . $type . '>' . "\r\n";
			} else {
				return '<' . $type . ' ' . self::getArgs($args) . '>' . $innerHTML . '</' . $type . '>';
			}
		}
	}

	/**
	* Get arguments to put in the element
	*
	* @param  array    $args  the arguments
	*
	* @return string   the arguments in string-format
	*/
	protected static function getArgs($args = array()) {
		array_walk($args,function($value,$key) { HTML::$_callback_arrays[] = (!is_int($key) ? $key . '="' . $value . '"' : $value); });
		$output = implode(" ",self::$_callback_arrays);
		self::$_callback_arrays = array();
		return $output;
	}

	/**
	* Create a link-element
	*
	* @param array $args    arguments you want to give to the link
	* @param mixed $inner   a value or method(string) to execute/return
	* 
	* @return string   the link in html
	*/
	public static function link($args = array(),$inner = null) {
		if (!empty($args['title'])) {
			if (strpos($args['href'],PROJECT_URL) !== false || substr($args['href'],0,1) == '/') {
				$args['title'] .= ' &laquo; ' . Accounts::getColumn('name',PROJECT_NAME);
			}
		} else {
			$args['title'] = strip_tags($inner) . ' &laquo; ' . Accounts::getColumn('name',PROJECT_NAME);
		}
		return self::element('a',$args,$inner);
	}

	/**
	* Create a image-element
	*
	* @param array $args 	arguments you want to give to the image
	* @param mixed $inner 	a value or method(string) to execute/return
	* 
	* @return string   the image in html
	*/
	public static function image($args = array(),$inner = null) {
		return self::element('img',$args,$inner);
	}

	/**
	 * Create a form-element
	 *
	 * @param array $args  the arguments for this element
	 * @param mixed $inner the 'inside' (fields etc) of the form-element
	 *
	 * @return string the html
	 */
	public static function form($args,$inner) {
		if (empty($args['method'])) {
			$args['method'] = 'POST';
		}
		if (empty($args['action'])) {
			$args['action'] = ' ';
		}
		return self::element('form',$args,$inner);
	}


	/**
	 * Create a formrow-element (<div><strong>{name}</strong>{inner}</div>)
	 *
	 * @param array $args   the arguments for this element
	 *
	 * @return string the html
	 */
	public static function formrow($name,$inner) {
		$s =  self::element('div',['class'=> 'form-group clearfix'],function() use ($name,$inner) {
			echo  self::element('label',['for'=>$name,'class' => 'col-sm-4 control-label'],function() use ($name) {
				echo $name;
			});
			echo $inner;
		});
		return $s;
	}

	/**
	 * Create a textarea-elementa
	 *
	 * @param array $args  the arguments for this element
	 * @param mixed $inner the 'inside' (string) of the textarea-element
	 *
	 * @return string the html
	 */
	public static function textarea($args,$inner = '') {
		if ($inner == '') {
			$inner = @$args['value'];
		}
		return self::element('textarea',$args,$inner);
	}


	/**
	 * Create a checkbox-elementa
	 *
	 * @param array $args  the arguments for this element
	 * @param mixed $inner the 'inside' (string) of the checkbox-element
	 *
	 * @return string the html
	 */
	public static function checkbox($args,$inner = '') {
		if (!isSet($args['class'])) {
			$args['class'] = '';
		}
		$args['class'] .= ' checkbox';
		$args['type'] = 'checkbox';
		return self::input($args);
	}

	
	/**
	 * Create a dropdown-element with given list
	 *
	 * @param array $args     the arguments for this element
	 * @param array $list     the list to fill this dropdown-element
	 * @param string $default fill a empty row with this name in the dropdown element
	 *
	 * @return string  the html of this dropdown-element
	 */
	public static function select($args,$list,$default = null) {
		return self::element('select',$args,function() use ($args,$list,$default) {
			if ($default !== null) {
				echo self::element('option',['value' => 0],$default);
			}
			if (!empty($list)) {
				foreach ($list AS $k=>$v) {
					if ($k == @$args['value']) {
						echo self::element('option',['value' => $k,'selected'],$v);
					} else {
						echo self::element('option',['value' => $k],$v);
					}
				}
			}
		});
	}

	/**
	 * Create a dropdown-element
	 *
	 * @param array  $args   the arguments for this element
	 * @param mixed $inner   the 'inside' (string) of the dropdown-element
	 *
	 * @return string the html
	 */
	public static function dropdown($args = array(),$inner = null) {
		return self::element('select',$args,$inner);
	}

	/**
	 * Create an option-element (part of a dropdown-element)
	 *
	 * @param array  $args   the arguments for this element
	 * @param mixed $inner   the 'inside' (string) of the option-element
	 *
	 * @return string the html
	 */
	public static function option($args = array(),$inner = null) {
		return self::element('option',$args,$inner);
	}

	/**
	 * Create an input-element
	 *
	 * @param array $args   the arguments for this element
	 *
	 * @return string the html
	 */
	public static function input($args = array()) {
		if (!isSet($args['style'])) {
			$args['style'] = '';
		}
		$args['style'] = 'margin: 0;' . $args['style'];
		if (empty($args['type'])) {
			$args['type'] = 'text';
		}
		return self::element('input',$args);
	}

	/**
	 * Create a hidden-element (input type="hidden")
	 *
	 * @param array $args   the arguments for this element
	 *
	 * @return string the html
	 */
	public static function hidden($args = array()) {
		$args = array_merge($args,array('type' => 'hidden'));
		return self::input($args);
	}

	/**
	 * Create a date-element (input class="date")
	 *
	 * @param array $args   the arguments for this element
	 *
	 * @return string the html
	 */
	public static function date($args = array()) {
		if (!isSet($args['class'])) {
			$args['class'] = '';
		}
		$args['class'] .= ' date datum';
		return '<div class="input-prepend date">' .self::input($args) . '<span class="add-on"><i class="icon-calender"></i></span></div>';
	}


	/**
	 * Create a email-element (input class="email")
	 *
	 * @param array $args   the arguments for this element
	 *
	 * @return string the html
	 */
	public static function email($args = array()) {
		if (!isSet($args['class'])) {
			$args['class'] = '';
		}
		$args['class'] .= ' email';
		return self::input($args);
	}


	/**
	 * Create a password-element (input type="password")
	 *
	 * @param array $args   the arguments for this element
	 *
	 * @return string the html
	 */
	public static function password($args = array()) {
		if (!isSet($args['class'])) {
			$args['class'] = '';
		}
		$args['type'] = 'password';
		$args['class'] .= ' password';
		return self::input($args);
	}

	

	/**
	 * Create a varchar-element (input type="text")
	 *
	 * @param array $args   the arguments for this element
	 *
	 * @return string the html
	 */
	public static function varchar($args = array()) {
		if (!isSet($args['class'])) {
			$args['class'] = '';
		}
		$args['class'] .= ' varchar';
		return self::input($args);
	}

	/**
	 * Create a boolean-element (yes/no dropdown)
	 *
	 * @param array $args   the arguments for this element
	 *
	 * @return string the html
	 */
	public static function bool($args = array()) {
		$drop = '<select name="' . @$args['name'] . '" id="' . @$args['name'] . '">';
		$list = array('Nee','Ja');
		$list = array_reverse($list,true);
		foreach ($list AS $id=>$l) {
			if (@$args['value'] == $id) {
				$drop .= '<option value="' . $id . '" selected>' . $l . '</option>';
			} else {
				$drop .= '<option value="' . $id . '">' . $l . '</option>';
			}
		}
		$drop .= '</select>';
		return $drop;
	}

	/**
	 * Create a integer-element (input class="int")
	 *
	 * @param array $args   the arguments for this element
	 *
	 * @return string the html
	 */
	public static function int($args = array()) {
		if (!isSet($args['class'])) {
			$args['class'] = '';
		}
		$args['class'] .= ' int';
		return self::input($args);
	}

	/**
	 * Create a price-element (input class="price")
	 *
	 * @param array $args   the arguments for this element
	 *
	 * @return string the html
	 */
	public static function price($args = array()) {
		if (!isSet($args['class'])) {
			$args['class'] = '';
		}
		$args['class'] .= ' price';
		return self::input($args);
	}

	/**
	 * Create a enum-element (test1/test2 dropdown)
	 *
	 * @param array $args   the arguments for this element
	 *
	 * @return string the html
	 */
	public static function enum($args,$list) {
		$drop = '<select name="' . @$args['name'] . '" id="' . @$args['name'] . '">';
		if (!empty($list)) {
			foreach ($list AS $l) {
				if (@$args['value'] == $l) {
					$drop .= '<option value="' . $l . '" selected>' . $l . '</option>';
				} else {
					$drop .= '<option value="' . $l . '">' . $l . '</option>';
				}
			}
		}
		$drop .= '</select>';
		return $drop;
	}

	/**
	 * Create a submit-button
	 *
	 * @param array $args   the arguments for this element
	 * @param mixed $inner  the 'inside' (string) of this element
	 *
	 * @return string the html
	 */
	public static function submit($args = array(),$inner = false) {
		if (empty($args['name'])) {
			$args['name'] = 'submit';
		}
		if (!isSet($args['class'])) {
			$args['class'] = '';
		}
		$args['class'] .= ' small btn btn-primary';//btn btn-medum btn-primary
		$args['class'] = String::trim($args['class']);
		$args['type'] = 'submit';
		return self::element('button',$args,self::element('span',array(),$inner));
	}

	/**
	 * Create a button
	 *
	 * @param array $args   the arguments for this element
	 * @param mixed $inner  the 'inside' (string) of this element
	 *
	 * @return string the html
	 */
	public static function button($args,$inner) {
		if (!isSet($args['class'])) {
			$args['class'] = '';
		}
		$args['class'] .= ' small button ';//btn btn-medum btn-primary';
		$args['class'] = String::trim($args['class']);
		$args['type'] = 'submit';
		return self::element('button',$args,self::element('span',array(),$inner));
	}

	/**
	 * Create a link that looks like a button
	 *
	 * @param array $args   the arguments for this element
	 * @param mixed $inner  the 'inside' (string) of this element
	 *
	 * @return string the html
	 */
	public static function buttonlink($args,$inner) {
		if (!isSet($args['class'])) {
			$args['class'] = '';
		}
		$args['class'] .= ' btn btn-primary';//btn btn-medum btn-primary';
		$args['class'] = String::trim($args['class']);
		return self::element('a',$args,self::element('span',array(),$inner));
	}

	public static function makeThumb($dir,$file,$x = '') {
		$e = String::split('.',$file);
		$ext = $e[count($e)-1];
		unset($e[count($e)-1]);
		$f = String::unsplit('.',$e);
		return '<img src="' . URL . '/uploads/' . String::trim($dir,'/') . '/' . $f . '' . $x . '.' . $ext . '" />';
	}



	/**
	 * Add File (js/css) to the files-variable
	 *
	 * @param string  $type  js/css
	 * @param string  $file  the url/location of the file
	 * @param integer $pos   position of this file
	 */
	public static function addFile($type,$file,$pos = 2) {
		if (!isSet(self::$files[$type])) {
			self::$files[$type] = [];
		}
		if (!isSet(self::$files[$type][$pos])) {
			self::$files[$type][$pos] = [];
		}
		if (!in_array($file,self::$files[$type][$pos])) {
			self::$files[$type][$pos][] = $file;
		}
		return true;
	}

	/**
	 * Create a script/link elementa of this js/css
	 *
	 * @param string $type  js/css
	 *
	 * @return string  file-elements (multiple) in html
	 */
	public static function makeFiles($type) {
		$content = '';
		if (!empty(self::$files[$type])) {
			foreach (self::$files[$type] AS $p) {
				if (!empty($p)) {
					foreach ($p AS $f) {
						if (substr($f,0,1) == '/') {
							$f = PROJECT_URL . $f;
						}
						if ($type == 'css') {
							$content .= self::element('link',array('rel'=>'stylesheet','href'=> $f));
						} elseif ($type == 'js') {
							$content .= self::element('script',array('src'=> $f),'');
						}
					}
				}
			}
		}
		return $content;
	}

	/**
	 * Create one script/link file with all the content of the saved files
	 *
	 * @param string  $type           js/css
	 * @param string  $name           name of the new file
	 * @param time    $timedifference time to know if you should use the cache file or not
	 * @param boolean $debug          if debug = true, then do not minify
	 *
	 * @return string  file-element with results
	 */
	public static function gatherFiles($type,$name = '123456',$timedifference = null,$debug = false) {
		if ($timedifference == null) {
			$timedifference = 86400;
		}
		$dir = 'cache/' . $name. '.' . $type;
		$cache_dir = P . $dir;
		$dir_long = 'cache/' . $name. '_long.' . $type;
		$cache_dir_long = P . $dir_long;
		$make = false;
		if (!file_exists($cache_dir)) {
			$make = true;
		}
		if (@filesize($cache_dir) < 100) {
			$make = true;
		}
		if ((time()-@filemtime($cache_dir)) > $timedifference) {
			$make = true;
		}
		$content = '';
		if ($make == true) {
			if (!empty(self::$files[$type])) {
				foreach (self::$files[$type] AS $p) {
					if (!empty($p)) {
						foreach ($p AS $f) {
							if (substr($f,0,1) == '/') {
								$content .= file_get_contents(rtrim(P,'/') . $f);
							} else {
								$content .= file_get_contents($f);
							}
						}
					}
				}
			}

			// minify
			$fp = fopen($cache_dir_long, 'w');
			fwrite($fp, $content);
			fclose($fp);
			$content = preg_replace( '#\s+#', ' ', $content );
			$content = preg_replace( '#/\*.*?\*/#s', '', $content );
			$content = preg_replace( '#^//.*?#s', ' ', $content );
			$content = str_replace( '; ', ';', $content );
			$content = str_replace( ': ', ':', $content );
			$content = str_replace( ' {', '{', $content );
			$content = str_replace( '{ ', '{', $content );
			$content = str_replace( ', ', ',', $content );
			$content = str_replace( '} ', '}', $content );

			$fp = fopen($cache_dir, 'w');
			fwrite($fp, $content);
			fclose($fp);
		}
		if ($debug == true) {
			$dir = $dir_long;
		}
		self::$files = array();
		if ($type == 'css') {
			return '<link rel="stylesheet" href="' . PROJECT_URL . '/' . $dir . '" />';
		} elseif ($type == 'js') {
			return self::element('script',array('src'=>PROJECT_URL . '/' . $dir),'');
		}
	}
}
?>