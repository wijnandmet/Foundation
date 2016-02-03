<?php
namespace Libraries\Frame;

use Libraries\Frame\App;
use Libraries\Types\Token;
use Libraries\Types\String;
use Exception;


/**
* -----------------------------------------------------------------------------
* Controller Library
* -----------------------------------------------------------------------------
* @author      Wijnand de Ridder
* @package     ALF
*
* @copyright   (c) 2015 Wijnand de Ridder
* @license     MIT
* -----------------------------------------------------------------------------
*
* About Controller-library
*
* This class is the base-class for a controller (and is exteded by the 
* controllers)
*
* Dependencies: App-Library, Token-Library and String-Library
*/
Class Controller {


	/**
	 * The breadcrumbs in the variable
	 *
	 * @var array
	 */
	protected static $breadcrumbs = array();

	/**
	 * Check that when you use a controller as a method, if that's possible or not.
	 *
	 * @param mixed $arguments  the arguments of this method
	 *
	 * @return mixed the result of the index-method of the used controller
	 */
	public function __invoke($arguments = []) {
		if (method_exists(get_class($this),'index')) {
			return $this->index(null);
		}
		throw new Exception('Not allowed to use an object as an function.');
	}

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
		throw new Exception('Couldn\'t find the static method ' . $name . ' in this class (' . get_class($this) . ').');
	}

	/**
	 * Gives an nice error when you try to load a static method that doesn't exist
	 *
	 * @param string $name      the name of the method
	 * @param mixed $arguments  the arguments of this method
	 *
	 * @return void
	 */
	public static function __callStatic($name,$arguments) {
		$self = new self();
		if (method_exists($self,$method)) {
			return $self->$method($arguments);
		}
		throw new Exception('Couldn\'t find the static method ' . $name . ' in this class (' . get_called_class() . ').');
	}

	/**
	 * Check that when you use a controller as a string, if that's possible or not.
	 *
	 * @return mixed the result of the index-method of the used controller
	 */
	public function __tostring() {
		if (method_exists(get_class($this),'index')) {
			return $this->index(null);
		}
		return Error::fatal(500,'Not allowed to use an object as an string.');
	}

	/**
	 * Returns a random strin
	 *
	 * @param integer $l length of the new randomized string
	 *
	 * @return string  the new randomizes string
	 */
	public static function random($l = 32) {
		$characters = 'fkjl8lsd87q2flhjv74892sfd89234sd9s83hfpalqlzqeri';
		$string = '';
		for ($i = 0; $i < $l; $i++) {
			$string .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $string;
	}

	/**
	 * Send an email
	 *
	 * @param string $title   the title of the email
	 * @param string $content the html/text of the email
	 * @param mixed $emails   the emailadress(es) to which this email must be send
	 *
	 * @return bool returns true when the email has been sent
	 */
	public static function mail($title,$message,$emails) {
		$to = '';
		if (is_array($emails)) {
			foreach($emails AS $k) {
				if ($to != '') { $to .= ',';}
				$to .= $k;
			}
		} else {
			$to = $emails;
		}
		$message .= '<br><br>' . App::language('default.mail-signature');


		if (file_exists(T . 'mail.tpl')) {
			ob_start();
			include(T . 'mail.tpl');
			$mailt = ob_get_contents();
			ob_end_clean();
		} else {
			$mailt = $message;
		}

		$mail = new Mail($title,$mailt);
		$a = explode(",",$to);
		foreach ($a AS $to) {
			$mail->to($to);
		}
		/*$mail->Subject = $title;
		$mail->MsgHTML($mailt);*/
		$mail->send();
		return true;
	}

	/**
	 * Get the basename of a class
	 *
	 * @param mixed $class the class/object from which you want the basename
	 *
	 * @return string the basename of the class/object
	 */
	public static function getClassBasename($class) {
		if (is_object($class)) $class = get_class($class);
		return basename(str_replace('.','/',str_replace('\\','/',$class)));
	}

	/**
	 * Add one breadcrumb to the breadcrumbs-variable
	 *
	 * @param array $arr the breadcrumb that needs to be added
	 */
	public static function addBreadcrumb($arr) {
		self::$breadcrumbs[] = $arr;
	}

	/**
	 * Get all the breadcrumbs from the breadcrumbs-variable
	 *
	 * @return array the array with all the breadcrumbs
	 */
	public static function getBreadcrumbs() {
		return self::$breadcrumbs;
	}

	/**
	 * Remove all the breadcrumbs from the breadcrumbs-variable
	 *
	 * @return void
	 */
	public static function removeBreadcrumbs() {
		self::$breadcrumbs = array();
	}

	/**
	 * Do a curl-request
	 *
	 * @param string $url     the url to which you want to do a request
	 * @param array  $options the options that you want to use to do a request
	 *
	 * @return string result of the request
	 */
	public static function curl($url, $options = array()){
		$ch = curl_init();
		$options = $options+array(
			CURLOPT_HEADER => false, // (voor debuggen aanzetten)
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => "1=1",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => $url,
			CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
			CURLOPT_VERBOSE => true
		);
		curl_setopt_array($ch, $options);
		$output = curl_exec($ch);
		if($output === false) {
    		throw new Exception('Curl error: ' . curl_error($ch));
		}
		curl_close($ch);
        return $output;
	}
	


	/**
	 * Create a token
	 *
	 * @param string $token the key of the new token
	 *
	 * @return void
	 */
	public static function initToken($token) {
		if (empty($_SESSION['token'])) {
			$_SESSION['token'] = array();
		}
		if (empty($_SESSION['token'][$token])) {
			$_SESSION['token'][$token] = array();
		}
		Token::bind($_SESSION[$token]);
	}

	/**
	 * Return a token
	 *
	 * @param string $token the key of the needed token
	 *
	 * @return string the needed token
	 */
	public static function getToken($token) {
		return Token::getToken($token);
	}

	/**
	 * Check a token
	 *
	 * @param string $token the key of the token that needs to be checked
	 *
	 * @return bool is the token validated?
	 */
	public static function checkToken($token) {
		$valid = Token::validate($_POST,$token);
		if (!$valid) {
			Token::reToken($token);
			return false;
		}
		return true;
	}
}
?>