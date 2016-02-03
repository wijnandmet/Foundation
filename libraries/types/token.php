<?php
namespace Libraries\Types;

Class Token {
	private static $_currentToken = array();
	//private static $_tokenLifetime = 3600; // could be a nice addition
	private static $_csrfStore = array();

	public static function generateToken($token) {
		self::$_currentToken[$token] = array(
			"key" => sha1(function_exists('openssl_random_pseudo_bytes') ? openssl_random_pseudo_bytes(32) : rand() . uniqid(microtime() . mt_rand(), true) . memory_get_usage(TRUE) . getmypid() . serialize($_SERVER)),
			"value" => sha1(function_exists('openssl_random_pseudo_bytes') ? openssl_random_pseudo_bytes(32) : rand() . uniqid(microtime() . mt_rand(), true) . memory_get_usage(TRUE) . getmypid() . serialize($_SERVER))
		);
		self::$_csrfStore = array();
		self::$_csrfStore[self::$_currentToken[$token]["key"]] = self::$_currentToken[$token]["value"];
	}

	public static function getToken($token) {
		if (empty(self::$_currentToken[$token])) {
			self::generateToken($token);
		}
		return self::$_currentToken[$token];
	}

	public static function reToken($token) {
		self::generateToken($token);
	}

	public static function validate($inputarr,$token) {
		if (empty(self::$_csrfStore)) {
			return false;
		}
		if (empty($inputarr) || !is_array($inputarr)) {
			return false;
		}
		foreach ($inputarr as $key => $token) {
			if (isset(self::$_csrfStore[$key]) && self::$_csrfStore[$key] === $token) {
				unset(self::$_csrfStore[$key]);
				return true;
			}
		}
		return false;
	}

	public static function bind(&$store) {
		self::$_csrfStore = &$store;
		if (empty(self::$_csrfStore)) {
			self::$_csrfStore = array();
		}
	}

	public static function setKeyStore($store) {
		self::$_csrfStore = $store;
		if (empty(self::$_csrfStore)) {
			self::$_csrfStore = array();
		}
	}

	public static function getKeyStore() {
		return self::$_csrfStore;
	}
}