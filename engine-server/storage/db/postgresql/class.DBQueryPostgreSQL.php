<?php
/**
 * @desc       Класс для выполнения запросов к PostgreSQL
 * @package    w framework
 * @category   engine/storage/db/postgresql
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       DBQuery, DBException
 */
class DBQueryPostgreSQL extends DBQuery {// postgresql не ниже чем 8.1

    protected function _connect() {
        $str = array();
        if($this->SETT['host']) $str[] = 'host='.$this->SETT['host'];
        if($this->SETT['user']) $str[] = 'user='.$this->SETT['user'];
        if($this->SETT['pass']) $str[] = 'password='.$this->SETT['pass'];
        if($this->SETT['dbname']) $str[] = 'dbname='.$this->SETT['dbname'];
        if($this->SETT['port']) $str[] = 'port='.$this->SETT['port'];
        if($this->SETT['char']) $str[] = 'options=\'--client_encoding='.$this->SETT['char'].'\'';
        
        $this->d = pg_connect(implode(' ',$str));
        if(!$this->d) throw new DBException('connection to the database with the parameters: "'.implode(' ',$str).'" - failed',E_USER_ERROR,0,__FILE__,__LINE__);
    }

    public function setOption($name,$value) {
        if(is_null($this->d)) $this->_connect();
        // пока будет пустой метод
    }

    public function setChar($char) {
        if(is_null($this->d)) $this->_connect();
        pg_set_client_encoding($this->d,$char);
    }

    protected function _escape($arg) {
        if(is_null($this->d)) $this->_connect();
        return pg_escape_string($this->d,$arg);
    }

    protected function _query($sql) {
        if(is_null($this->d)) $this->_connect();
	$res = pg_query($this->d,$sql);
	if(!$res) {
            throw new DBException(pg_last_error($this->d).'; QUERY - '.$sql,E_USER_ERROR,0,__FILE__,__LINE__);
	}
	return $res;
    }

    protected function _mquery($sql) {
        return $this->_query($sql);
    }

    protected function  getResult($q) {
        return new DBResultPostgreSQL($this->PRO,$q);
    }

    public function lastInsertId() {
        if(is_null($this->d)) $this->_connect();
        $res = $this->_query('SELECT LASTVAL() as last_id');
        $arg = pg_fetch_result($res,0,'last_id');
        pg_free_result($res);
        return $arg;
    }

    public function affected_rows() {
        if(is_null($this->d)) $this->_connect();
	return pg_affected_rows($this->d);
    }

    protected function _begin() {
        if(is_null($this->d)) $this->_connect();
        return (bool)$this->_query('BEGIN WORK');
    }

    protected function _commit() {
        if(is_null($this->d)) $this->_connect();
        return (bool)$this->_query('COMMIT');
    }

    protected function _rollback() {
        if(is_null($this->d)) $this->_connect();
        return (bool)$this->_query('ROLLBACK');
    }

    public function __destruct() {
	if(!is_null($this->d)) pg_close($this->d);
    }

}
?>
