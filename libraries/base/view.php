<?php
namespace Libraries\Frame;

use Libraries\Types\String;

/**
* -----------------------------------------------------------------------------
* View Library
* -----------------------------------------------------------------------------
* @author      Wijnand de Ridder
* @package     ALF
*
* @copyright   (c) 2015 Wijnand de Ridder
* @license     MIT
* -----------------------------------------------------------------------------
*
* About View Library
*
* The library by which you can select and fill views
*
* Dependencies: String Library
*/

Class View {

	/**
	 * The vars for the current view
	 *
	 * @var array
	 */
	public $_vars = array();

	/**
	 * The data for this view
	 *
	 * @var array
	 */
	public $_data = array('url' => null,'path' => null);

	/**
	* Initialize the view
	*
	* @param  string  $path the path of the view/template
	* @param  array   $args the arguments for the view (set)
	*/
	public function __construct($path,$args = array()) {
		$this->_data['path'] = $path;
		$this->_data['url'] = T . String::replace('.',DS,$path) . '.phtml';
		$this->set($args);
	}

	/**
	* Set a var from this view
	*
	* @param  string  $key   key/index of the var
	* @param  mixed  $value  value of the val
	*/
	public function __set($key,$value) {
		$this->_vars[$key] = $value;
	}

	/**
	* get a var from this view
	*
	* @param  string  $key   key/index of the var
	* 
	* @return string
	*/
	public function __get($key) {
		return $this->_vars[$key];
	}

	/**
	* set an array in vars
	*
	* @param  array  $array   the array with vars to set
	* 
	* @return void
	*/
	public function set($array = null) {
		if (!empty($array)) {
			foreach ($array AS $key=>$value) {
				$this->_vars[$key] = $value;
			}
		}
	}

	/**
	* render the view
	*
	* @return string
	*/
	public function render() {
		ob_start();
		if (!empty($this->_vars)) {
			foreach ($this->_vars AS $k=>$v) {
				$$k = $v;
			}
		}
		if (file_exists($this->_data['url'])) {
    		require($this->_data['url']);
    	} else {
    		abort(404,"The view '" . $this->_data['path'] . "' couldn't be loaded.");
    	}
    	return ob_get_clean();
	}

	/**
	 * Load another view and return.
	 *
	 * @param string $str  the other view, you want to load
	 *
	 * @return string result
	 */
	public function load($str) {
		$view = new View($str);
		$view->set($this->_vars);
		return $view->render();
	}
}