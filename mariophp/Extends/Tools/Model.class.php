<?php
/**
 * @Author: Administrator
 * @Date:   2016-09-06 23:41:52
 * @Last Modified by:   Administrator
 * @Last Modified time: 2016-09-10 08:45:27
 */
class Model{
	public static $link = null;
	//保存表名
	protected $table = null;
	public $opt = array(); 

	public static $sqls = array();

	public function __CONSTRUCT($table=null){
		$this->table = !is_null($table) ? C('DB_PREFIX') . $table : C('DB_PREFIX') . $this->table;
		// p($this->table);
		self::__connect();

		$this->_initSql();
	}
	public function field($field){
		$this->opt['field'] = $field;
		return $this;
	}
	public function where($where){
	 	$str = '';
		if(is_array($where)){
				foreach($where as $k=>$r){
				 if(!is_int($r)){	
					$str.="{$k}='{$r}' and ";
				 }else{
				 	$str.="{$k}={$r} and ";
				 }
				 
			}
			$str = rtrim($str,' and ');
			$this->opt['where'] = " WHERE ".$str;
			return $this;
		}
		$this->opt['where'] = " WHERE " . $where;
		return $this;
	}
	public function order($order){
		$this->opt['order'] = " ORDER BY ". $order;
		return $this;
	}
	public function limit($limit){
		$this->opt['limit'] = " LIMIT " . $limit;
		return $this;
	}
	/**
	 * [find 找出一条（一维数组）]
	 * @return [type] [返回数组中的当前单元]
	 */
	public function find(){
		$data = $this->limit(1)->all();
		return $data = current($data);
	}
	/**
	 * [all 拼装sql语句]
	 * @return [type] [description]
	 */
	public function all(){
		$sql = "SELECT " . $this->opt['field'] . " FROM " . $this->table . $this->opt['where'] . $this->opt['group'] . $this->opt['having'] . $this->opt['order'] .$this->opt['limit'];
		return $this->query($sql);	
	}
	/**
	 * [query 查询函数]
	 * @param  [type] $sql [sql语句]
	 * @return [type]      [查询结果]
	 */
	public function query($sql){
		self::$sqls[] = $sql;
		$link=self::$link;
		$result = $link->query($sql);
		if($link->errno) E('mysql错误' . $link->error .'<br/>SQL:' . $sql);
		$rows = array();
		while($row = $result->fetch_assoc()){
			$rows[] = $row;
		}
		$result->free();
		$this->_initSql();
		return $rows;
	}

	public function exe($sql){
		self::$sqls[]=$sql;
		$link = self::$link;
		$bool = $link -> query($sql);
		$this->_initSql();
		if(is_object($bool))
			E('请用query执行查询语句');
		if($bool){
			return $link->insert_id ? $link->insert_id : $link->affected_rows;
		}else{
			E('mysql错误' . $link->error . '<br/>SQL:' . $sql);
		}
	}

	public function delete(){
		if (empty($this->opt['where'])) E('删除语句必须有where条件');

		$sql = 'DELETE FROM ' . $this->table . ' ' . $this->opt['where'];
		return $this->exe($sql);
	}

	/**
	 * [_initSql 初始化sql表达式为空]
	 * @return [type] [description]
	 */
	private function _initSql(){
		$this->opt = array(
			'field' => '*',
			'where' => '',
			'group' => '',
			'having' => '',
			'order' => '',
			'limit' => '',
			);
	}
	/**
	 * [__connect 数据库连接]
	 * @return [type] [null]
	 */
	private static function __connect(){
		if(is_null(self::$link)){
			if(C('DB_DATABASE')=='') E('请先配置数据库');
			$link =  new mysqli(C('DB_HOST'), C('DB_USER'), C('DB_PASSWORD'), C('DB_DATABASE'));

			if($link->connect_error) E('数据库连接错误，请检查配置项');
			$link->set_charset(C('DB_CHARSET'));
			self::$link = $link;
		}
	}
	//批量转义
	private function deepslashes($data){
		//判断$data的表现形式,并且需要处理空的情况
		if (empty($data)){
			return $data;
		}
		return is_array($data) ? array_map('deepslashes', $data) : addslashes($data) ;

	}

	//批量实体转义
	private function deepspecialchars($data){
		if (empty($data)){
			return $data;

		}
		return is_array($data) ? array_map('deepspecialchars', $data) : htmlspecialchars($data);

	}

	public function add($data=NULL){
		if(is_null($data)) $data=$_POST;
		// 
		$fields = '';
		$values = '';

		foreach ($data as $k => $v) {
			$k = deepslashes($k);
			$k = deepspecialchars($k);
			$v = deepslashes($v);
			$v = deepspecialchars($v);
			$fields.= '`' . $k . '`,';
			$values.= "'" . $v . "',";
		}

		$fields =trim($fields , ',');
		$values =trim($values , ',');
		// p($data);die;

		$sql = "INSERT INTO " . $this->table . ' (' . $fields .') VALUE (' . $values . ')';
		return $this->exe($sql);
		 // echo $sql;die; 

	}

	public function update($data = NULL){
		if(empty($this->opt['where'])) E('update语句必须要where条件');
		if(is_null($data)) $data=$_POST;

		$values = '';

		foreach ($data as $k => $v) {
			$values.= "`" . $k . '`=' . "'" . $v . "',";
		}

		$values = trim($values,',');

		$sql = "UPDATE " . $this->table . " SET " . $values . $this->opt['where'];
		return $this->exe($sql);
	}
}