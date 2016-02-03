<?php 
namespace Libraries\DB;
include_once("table.php");

use PDO;
use stdClass;
use App;
use Filter;
use ExceptionFoundation;
use Exception;
use Permalinks;
use Carbon;

Class Foundation2 {

	protected $_current; // current table-object
	protected $_special;
	protected $_specialArgs = [];
	protected $_lastExecution;
	protected $_lastPDO;
	protected $_alias = 'default';
	protected $_table;
	protected $_args = ['where' => [],'wherebind' => [],'order' => [],'limit' => null];
	protected $argscount = 0;
	protected static $commits = [];
	protected $_subitems = [];
	protected static $_link = [];

	public function __construct($id = null) {
		if ($this->_table == '') {
			throw new DBException('No table found.');
		}
		if ($id !== null) {
			$a = $this->where(['id' => $id])->first();
		}
		return $this;
	}

	public function __debugInfo() {
		if (!empty($this->_current)) {
			return $this->_current->toArray();
		}
	}

	public function __tostring() {
		return var_export($this->_current->toArray(),true);
	}
	
	public function __get($key) {
		try {
			return $this->_current->$key;
		} catch (Exception $e) {
			throw new DBException($e);
		}
	}

	public function __set($key,$value) {
		$this->_current->$key = $value;
	}

	public static function host(array $host,$alias = 'default') {
		static::$_link[$alias] = ['dns' => $host['dns'],'user' => $host['user'],'pass' => $host['pass']];
	}


	public static function start() {
		$t = new static();
		return $t;
	}

	public function where($column,$value = null) {
		if (!empty($column)) {
			if (!is_array($column)) {
				$this->_args['where'][] = " `" . $column . "` " . $c . " :" . $column . $this->argscount . " ";
				$this->_args['wherebind'][] = ['column' => $column . $this->argscount,'value' => $value];
				$this->argscount++;
			} else {
				foreach ($column AS $k=>$v) {
					$this->_args['where'][] = " `" . $k . "` = :" . $k . $this->argscount . " ";
					$this->_args['wherebind'][] = ['column' => $k . $this->argscount,'value' => $v];
					$this->argscount++;
				}
			}
		}
		return $this;
	}

	public function whereIn($column,$value) {
		$this->_args['where'][] = " `" . $column . "` IN (" . String::unsplit(',',array_filter($value, 'is_integer')) . ") ";
		$this->_args['wherebind'][] = [];
		$this->argscount++;
		return $this;
	}

	public function whereNotIn() {

	}

	public function whereBetween() {

	}

	public function whereNot() {
		$this->_args['where'][] = " `" . $column . "` != " . $value . " ";
		$this->_args['wherebind'][] = [];
		$this->argscount++;
		return $this;
	}

	public function order($field,$type = 'ASC') {
		$this->_args['order'][] = " " . $field . " " . $type;
		return $this;
	}

	public function group($field) {
		$this->_args['group'][] = $field;
		return $this;
	}

	public function limit($num1,$num2 = null) {
		if ($num2 === null) {
			$this->_args['limit'] = " LIMIT " . $num1;
			return $this;
		}
		$this->_args['limit'] = " LIMIT " . $num1 . ", " . $num2;
		return $this;
	}

	public function belongsTo($class,$column) {
		if (!isSet($this->_current->$column)) {
			throw new \ExceptionFoundation('Column "' . $column . '" not found');
		}
		if ($column == 'parent_id' && $this->_current->$column == 0) {
			return $this;
		}
		return new $class($this->_current->$column);
	}
	public function hasMany($class,$column) {
		$item = new $class();
		return $item->where([$column => $this->_current->id]);
	}

	public function insert($args = []) {
		$a = '';
		$b = '';
		foreach ($args AS $k=>$v) {
			$a .= " `" . $k . "`,";
			$b .= ":" . $k . ",";
			/*if (isSet($this->_schema[$k]['type']) && $this->_schema[$k]['type'] == 'date') {
				$c = Carbon::createFromFormat('d-m-Y',$v);
				$v = $c->__toString();
			} elseif (isSet($this->_schema[$k]['type']) && $this->_schema[$k]['type'] == 'datetime') {
				$c = Carbon::createFromFormat('d-m-Y H:i:s',$v);
				$v = $c->__toString();
			}*/
			$where[$k] = $v;
		}
		$a = trim($a,',');
		$b = trim($b,',');

		$sql = "INSERT INTO `" . $this->_table . "` (" . $a . ") VALUES (" . $b . ")";
		$this->query($sql,$where);
		return new static($this->_lastPDO->lastInsertId());
	}

	public function delete($args = []) {
		if (!empty($args)) {
			$item = $this->where($args)->first();
			if (isSet($item->id)) {
				$id = $item->id;
				$table = $item->_table;
			}
		} elseif (empty($args) && !empty($this->_current)) {
			$id = $this->_current->id;
			$table = $this->_table;
		}

		if (!isSet($id)) {
			throw new DBException('The row could\'t be removed.');
		}
		$this->_special = "DELETE FROM `" . $table . "` WHERE id = :id";
		$this->_specialArgs = ['id' => $id];
		$this->query();
		return $this;
	}

	public function query() {
		// create query
		if ($this->_special !== null) {
			$query = $this->_special;
		} else {
			$query = " SELECT * FROM " . $this->_table . " ";
			if (!empty($this->_args['where'])) {
				$query .= " WHERE 1=1 ";
				foreach ($this->_args['where'] AS $k=>$where) {
					$query .= " AND " . $where . " ";
				}
			}
			if (!empty($this->_args['order'])) {
				$i = 0;
				foreach ($this->_args['order'] AS $o) {
					if ($i == 0) {
						$query .= " ORDER BY " . $o;
					} else {
						$query .= " , " . $o;
					}
					$i++;
				}
			}
			if (!empty($this->_args['group'])) {
				$i = 0;
				foreach ($this->_args['group'] AS $o) {
					if ($i == 0) {
						$query .= " GROUP BY " . $o;
					} else {
						$query .= ", " . $o;
					}
					$i++;
				}
			}
			if (!empty($this->_args['limit'])) {
				$query .= " " . $this->_args['limit'];
			}
		}
		
		// prepare
		$pdo = new PDO(
			static::$_link[$this->_alias]['dns'], 
			static::$_link[$this->_alias]['user'], 
			static::$_link[$this->_alias]['pass'], 
			[
	            PDO::ATTR_PERSISTENT => true,
	            PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
	            PDO::ATTR_EMULATE_PREPARES => false,
	            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        	]
        );
        if (!$stmt = $pdo->prepare($query)) {
            throw new DBException($_pdo->errorInfo());
        }
        
        // bind params & execute
        if ($this->_special === null) {
	        if (!empty($this->_args['where'])) {
	        	foreach ($this->_args['where'] AS $i=>$where) {
	        		if (!empty($this->_args['wherebind'][$i])) {
		        		$kn = $this->_args['wherebind'][$i]['column'];
		        		$k = preg_replace("/[^a-z]+/","",strtolower($kn));//Filter::toAlfa($kn);
		        		$v = $this->_args['wherebind'][$i]['value'];
		        		/*if (isSet($this->_schema[$k]['encrypt']) && $this->_schema[$k]['encrypt'] == 'md5') {
							$v = md5($v);
						}*/
		        		$stmt->bindValue(':' . $kn,$v,PDO::PARAM_STR);
		        	}
	        	}
	        }
	        if(!$stmt->execute()) {
	            throw new DBException($this->_pdo->errorInfo());
	        }
	    } else {
	    	$args = $this->_specialArgs;
	    	if (!empty($args)) {
	        	foreach ($args AS $kn=>$v) {
	        		$k = preg_replace("/[^a-z]+/","",strtolower($kn));//Filter::toAlfa($kn);
	        		/*if (isSet($this->_schema[$k]['encrypt']) && $this->_schema[$k]['encrypt'] == 'md5') {
						$v = md5($v);
					}*/
	        		$stmt->bindValue(':' . $kn,$v,PDO::PARAM_STR);
	        	}
	        }
	        if(!$stmt->execute()) {
	            throw new DBException($this->_pdo->errorInfo());
	        }
	    }
        
        // Wrap everything up
        $this->_args = [];
        $this->_special = null;
        $this->_specialArgs = [];
        $this->_lastExecution = $stmt;
        $this->_lastPDO = $pdo;
		return $this;
	}

	public function results($type = 'array') {
		if (!$c = $this->_lastExecution) {
            throw new DBException($this->_pdo->errorInfo());
        }
       	if ($type == 'oop') {
            $arr = $c->fetchAll(\PDO::FETCH_OBJ);
        } else {
            $arr = $c->fetchAll(\PDO::FETCH_ASSOC);
        }
        $c->closeCursor();
        return $arr;
	}

	public function table($table) {
		$this->_table = $table;
		return $this;
	}

	public function fetch($arr = []) {
		$a = $this->where($arr)->query()->results();
		return $this->toObject($a);
	}

	public function fetchKeyValue($where = [],$key = 'id',$value = 'title') {
		$arr = [];
		$results = $this->where($where)->fetch();
		if (!empty($results)) {
			foreach ($results AS $obj) {
				$arr[$obj->$key] = $obj->$value;
			}
		}
		return $arr;
	}

	public function first($arr = []) {
		$items = $this->where($arr)->limit(1)->query()->results();
		if (empty($items)) {
			return false;
		}
		$i = $this->toObject($items);
		
		$this->_current = reset($i);
		return $this;
	}

	public function last() {
		$items = $this->where($arr)->limit(1)->query()->results();
		if (empty($items)) {
			return false;
		}
		$i = $this->toObject($items);
		$this->_current = end($i);
		return $this;
	}

	public function saveWith($args,$commit = false) {
		if (!empty($args)) {
			foreach ($args AS $k=>$v) {
				$this->_current->$k = $v;
			}
		}
		return $this->save();
	}

	public function save($commit = false) {
		if (empty($this->_current->toArray())) {
			throw new DBException("The results couldn't be saved.");
		}
		$where = [];
		$vars = $this->_current->toArray();
		$w = " WHERE id = :id";
		$set = '';
		foreach ($vars AS $k=>$v) {
			if ($k != 'id') {
				$set .= " `" . $k . "` = :" . $k . "," ;
			}
			$where[$k] = $v;
		}
		$set = trim($set,',');
		$sql = "UPDATE " . $this->_table . " SET " . $set . " " . $w . " ";
		if ($commit === true) {
			self::$commits[] = [$t->_table,$sql,$where];
			return true;
		}
		$this->_special = $sql;
		$this->_specialArgs = $where;
		$this->query();
		return true;
	}

	public function toArray() {
		return $this->_current->toArray();
	}

	public function toJson() {
		return $this->_current->toJson();
	}

	public function toObject($data = null) {
		if ($data == null && !empty($this->_current)) {
			$data = $this->_current;
		}
		$nitems = [];
		if (!empty($data)) {
			foreach ($data AS $k=>$v) {
				$a = new Foundation2Item($this->_table,$v);
				$nitems[] = $a;
			}
			return $nitems;
		}
		return false;
	}

	public function execute() {}
	public function rawquery() {}
	public function queryForCommit() {}
	public function commit() {}
}