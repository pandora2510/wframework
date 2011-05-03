<?php 
/**
 * @desc       Базовый класс для выполнения запросов к реляционным БД
 * @package    w framework
 * @category   engine/storage/db
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, ErrorException, DBException
 * @version    0.3.0
 */
abstract class DBQuery extends Object {

 protected $d = null;
	
    public function __init($a) {
	try {
            //$this->_connect();
	} catch(DBException $e) {
            $this->PRO['error']->handlerror($e);
	}
    }

    public function getResurs() {
        return $this->d;// передавать ссылку
    }
	
    abstract protected function _connect();
	
    abstract public function setOption($name,$value);
	
    abstract public function setChar($char);
	
    final public function query() {
    	$arg = func_get_args();
    	$sql = array_shift($arg);
    	return $this->query_a($sql,$arg);
    }
	
    final public function query_a($sql,array $arg) {
	try {
            $sql = $this->_sprintf($sql,$arg);
            $type = substr($sql,0,3);
            return in_array($type,array('SEL','SHO','DES','EXP'))?$this->getResult($this->_query($sql)):$this->_query($sql);
	} catch(DBException $e) {
            $this->PRO['error']->handlerror($e);
            return null;
	}
    }

    abstract protected function getResult($q);
	
    final public function mquery() {
	$arg = func_get_args();
	$sql = array_shift($arg);
	return $this->mquery_a($sql,$arg);
    }

    final public function mquery_a($sql,array $arg) {
        try {
            $sql = $this->_sprintf($sql,$arg);
            return (bool)$this->_mquery($sql);
	} catch(DBException $e) {
            $this->PRO['error']->handlerror($e);
            return null;
	}
    }
	
    final public function escape($arg) {
        try {            
            if(is_array($arg) or is_object($arg)) {
                foreach($arg as $k=>$val) {
                    $arg[$k] = $this->_escape($val);
                }
                return $arg;
            } else {
                return $this->_escape($arg);
            }
        } catch(DBException $e) {
            $this->PRO['error']->handlerror($e);
            return null;
        }
    }
	
    protected function _sprintf($sql,array $arg) {
	$arg = $this->escape($arg);
        return vsprintf($sql,$arg);
    }
	
    abstract protected function _escape($arg);

    abstract protected function _query($sql);
	
    abstract protected function _mquery($sql);

    abstract public function lastInsertId();
	
    // st 0 - begin, 1 - commit, 2 - roll
    public function transaction($st=0) {
	switch($st) {
            case 0: return $this->_begin(); break;
            case 1: return $this->_commit(); break;
            case 2: return $this->_rollback(); break;
	}
    }
	
    abstract protected function _begin();
	
    abstract protected function _commit();
	
    abstract protected function _rollback();
	
    abstract public function __destruct();
	
}
?>
