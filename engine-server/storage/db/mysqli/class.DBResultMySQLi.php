<?php
/**
 * @desc       Класс для обработки результатов с mysqli
 * @package    w framework
 * @category   engine/storage/db/mysqli
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       DBResult, DBIteratorMySQLi
 */
class DBResultMySQLi extends DBResult {

 const ASSOC = MYSQLI_ASSOC;
 const NUM = MYSQLI_NUM;
 const BOTH = MYSQLI_BOTH;
 public $type = MYSQLI_ASSOC;

    public function  getIterator() {
        return new DBIteratorMySQLi($this->res,$this->type);
    }

    public function all() {// добавить iterator_to_array
        $this->res->data_seek(0);
        $this->type = in_array($this->type,array(MYSQLI_ASSOC,MYSQLI_BOTH,MYSQLI_NUM))?$this->type:MYSQLI_ASSOC;
        if(method_exists($this->res,'fetch_all')) return $this->res->fetch_all($this->type);
         else return iterator_to_array($this,true);
    }

    public function  __destruct() {
        if(!is_null($this->res)) $this->res->close();
    }

}
?>
