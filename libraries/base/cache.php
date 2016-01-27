<?php 
Namespace Libraries\Base;

\Libraries\Files\File;

Class Cache {

	protected static $dir = 'cache';
	
	protected static $cacheTime = (5*60);

	public static function get($key, $group = 'default') {
		$full_file = self::$dir . '/' . serialize($group . $key);
		if ($file = File::load($full_file)) {
			if (Date::dateDiff(Date::now(),$file->getModifiedDate(),'s') > self::$cacheTime) {
				File::remove($full_file);
				return false;
			}
			return unserialize($file->content());
		}
		return false;
	}

	public static function save($data, $key, $group = 'default') {
		File::create(self::$dir . '/' . serialize($group . $key));
		return true;
	}

}
?>