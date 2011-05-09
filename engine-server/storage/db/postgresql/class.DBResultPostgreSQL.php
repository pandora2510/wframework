<?php
/**
 * @desc       ...
 * @package    w framework
 * @category   engine/storage/db/postgresql
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       DBResult, DBIteratorPostgreSQL
 */
class DBResultPostgreSQL extends DBResult {

 const ASSOC = PGSQL_ASSOC;
 const NUM = PGSQL_NUM;
 const BOTH = PGSQL_BOTH;
 public $type = PGSQL_ASSOC;

    public function  getIterator() {
        if(!is_resource($this->res)) return new EmptyIterator();        
        return new DBIteratorPostgreSQL($this->res,$this->type);
    }

    public function all() {
        pg_result_seek($this->res,0);
        //$this->type = in_array($this->type,array(PGSQL_ASSOC,PGSQL_BOTH,PGSQL_NUM))?$this->type:PGSQL_ASSOC;
        return pg_fetch_all($this->res);
    }

    public function  __destruct() {
        if($this->res) pg_free_result($this->res);
    }

}
?>
