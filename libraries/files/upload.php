<?php
namespace Libraries\Types;

/**
* -----------------------------------------------------------------------------
* File Library
* -----------------------------------------------------------------------------
* @author      Wijnand de Ridder
* @package     ALF
*
* @copyright   (c) 2015 Wijnand de Ridder
* @license     MIT
* -----------------------------------------------------------------------------
*
* About File Library
*
* This library is the library for handling files.
*/
Class File {

	protected $_file;
	public $_error;
	protected $_name;

	public static function init($name) {
		if (!empty($_FILES[$name]['name'])) {
			$t = new self();
			$t->_name = $name;
			$t->_file = $_FILES[$name];
			return $t;
		}
		return false;
	}

	public function isValid($arr = []) {
		//if ($_FILES['upfile']['size'] > 1000000) {
		if (!empty($this->_file['error'])) {
			switch ($this->_file['error']) {
		        case UPLOAD_ERR_OK:
		            break;
		        case UPLOAD_ERR_NO_FILE:
		            $this->_error = ('No file sent.');
		        case UPLOAD_ERR_INI_SIZE:
		        case UPLOAD_ERR_FORM_SIZE:
		            $this->_error = ('Exceeded filesize limit.');
		        default:
		            $this->_error = ('Unknown errors.');
	        }
    	}

    	if (!empty($arr['max']) && $this->_file['size'] > $arr['max']) {
        	$this->_error = 'Exceeded filesize limit.';
    	}

    	if (empty($this->_error)) {
    		return true;
    	}
    	return false;
	}

	public function getError() {
		return $this->_error;
	}
	
	public function getExtension() {
		$e = explode('.',$this->_file['name']);
		return Arr::last($e);
	}

	public function getName() {
		return $this->_file['name'];
	}

	public function move($dir, $fileName) {
		if (!move_uploaded_file(
	        $this->_file['tmp_name'],
	        $dir . $fileName
	    )) {
			throw new Execption('The file couldn\'t be uploaded.');
		}
		return true;
	}

	public function resize($f) {
		$resize = new \Libraries\Upload\Resize($f);
		return $resize;
	}
}
?>