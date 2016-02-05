<?php 
Namespace Libraries\Files;

use \Libraries\Types\String;

Class File {

	protected $file;
	protected $rights = ['file' => 0700,'dir' => 0644];

	public static function load($file) {
		if (!is_file($file) || !is_readable($file) || !is_writable($file)) {
			return false;
		}
		$this->file = $file;
		return $this;
	}

	public function getModifiedDate() {
		return filectime($this->file);
	}

	public function remove($file = null) {
		if ($file === null) {
			$file = $this->file;
		}
		if (empty($file)) {
			abort('There was no file to delete.');
		}
		if (!unlink($file)) {
			abort('The file ' . $file . ' couldn\'t be deleted.');
		}
	}
	
	public function content() {
		return file_get_contents($this->file);
	}

	public function save($file,$content) {
		if (self::load($file)) {
			abort('The file ' . $file . ' already exists');
		}
		$e = String::split('/',$file);

		$f = array_pop($e);
		$dir = ROOT;
		foreach ($e AS $v) {
			$dir .= $v . '/';
			if (is_dir($v)) {
				chdir($dir);
				continue;
			}
			try {
				mkdir($e,$this->rights['dir']);
				chdir($dir);
			} catch (Exception $e) {
				abort('Directory ' . $dir . ' couldn\'t be created.');
			}
		}
		chdir(ROOT);
		if (is_file($dir . $f)) {
			$fp = fopen($dir . $f, 'w');
		} else {
			$fp = fopen($dir . $f, 'a');
		}
		try {
			fwrite($fp, $content);
			fclose();
			chmod($dir . $f, $this->rights['file']);
		} catch (Exception $e) {
			abort('The file ' . $dir . $f . ' couldn\'t be created.');
		}
		return true;
	}

	public static function removeAllFrom($dir) {
		$files = glob(($dir . '/*');
		foreach($files as $file){
			if(is_file($file)) {
				unlink($file);
			} elseif (is_dir($file)) {
				self::removeAllFrom($dir . '/' . $file);
			}
		}
		return true;
	}
}
?>
