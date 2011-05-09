<?php 
/**
 * @desc       Класс для выполнения запросов к MySQL через mysqli
 * @package    w framework
 * @category   engine/storage/db/mysqli
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       DBQuery, mysqli, DBException, DBResultMySQLi
 */
class DBQueryMySQLi extends DBQuery {
	
 const OPT_CONNECT_TIMEOUT = MYSQLI_OPT_CONNECT_TIMEOUT;
 const OPT_LOCAL_INFILE = MYSQLI_OPT_LOCAL_INFILE;
 const INIT_COMMAND = MYSQLI_INIT_COMMAND;
 const READ_DEFAULT_FILE = MYSQLI_READ_DEFAULT_FILE;
 const READ_DEFAULT_GROUP = MYSQLI_READ_DEFAULT_GROUP;
	
    protected function _connect() {
        $this->d = new mysqli($this->SETT['host'],$this->SETT['user'],$this->SETT['pass'],$this->SETT['dbname'],$this->SETT['port'],$this->SETT['socket']);
	$this->setChar($this->SETT['char']);
	if(mysqli_connect_errno()) {
            throw new DBException(mysqli_connect_error(),mysqli_connect_errno(),0,__FILE__,__LINE__);
	}
    }
	
    public function setOption($name,$value) {
        if(is_null($this->d)) $this->_connect();
    	$this->d->options($name,$value);
    }
	
    public function setChar($char) {
        if(is_null($this->d)) $this->_connect();
	$this->d->set_charset($char);
    }
	
    protected function _escape($arg) {
        if(is_null($this->d)) $this->_connect();
        return $this->d->real_escape_string($arg);        
    }
	
    protected function _query($sql) {
        if(is_null($this->d)) $this->_connect();
	$res = $this->d->query($sql,MYSQLI_STORE_RESULT);
	if($this->d->errno) {
            throw new DBException($this->d->error.'; QUERY - '.$sql,$this->d->errno,0,__FILE__,__LINE__);
	}
	return $res;
    }

    protected function  getResult($q) {
        if($q instanceof mysqli_result) return new DBResultMySQLi($this->PRO,$q); else return new EmptyIterator();
    }

    // небезопасный метод
    protected function _mquery($sql) {
        if(is_null($this->d)) $this->_connect();
	$res = $this->d->multi_query($sql);
	if($this->d->errno) {
            throw new DBException($this->d->error.'; QUERY - '.$sql,$this->d->errno,0,__FILE__,__LINE__);
	}
	return $res;
    }
	
    public function lastInsertId() {
        if(is_null($this->d)) $this->_connect();
	return $this->d->insert_id;
    }

    public function affected_rows() {
        if(is_null($this->d)) $this->_connect();
	return $this->d->affected_rows;
    }

    protected function _begin() {
        if(is_null($this->d)) $this->_connect();
        return $this->d->autocommit(flase);
    }

    protected function _commit() {
        if(is_null($this->d)) $this->_connect();
        return $this->d->commit();
    }

    protected function _rollback() {
        if(is_null($this->d)) $this->_connect();
        return $this->d->rollback();
    }
	
    public function __destruct() {
	if(!is_null($this->d)) $this->d->close();
    }
	
}
?>
