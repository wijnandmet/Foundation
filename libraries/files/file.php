<?php 
Namespace Libraries\Files;

Class File {

	protected $file;

	public static function load($file) {
		return 'file';
	}

	public function getModifiedDate() {
		return getModifiedDate($this->file);
	}

	public function remove($file = null) {
		// if $file == null, remote $this->file, else remove $file
	
	}
	
	public function content() {
		return file_get_contents($this->file);
	}

	public function create($file) {
		// create file with dirs (if not exists)
	}
}
?>
