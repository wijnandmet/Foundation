<?php 

Namespace Models;

use \Libraries\Base\DB;

Class Page extends DB {

	private $table = 'pages';

	private $cache = [
		'groups' => ['pages','menus']
	];

}