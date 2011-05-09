<?php
/**
 * @desc       Iterator для обработки результатов Выборки
 * @package    w framework
 * @category   engine/storage/db/postgresql
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Iterator
 */
class DBIteratorPostgreSQL implements Iterator {

 private $res = null;
 private $type = PGSQL_ASSOC;
 private $p = 0;
 private $length = 0;

    public function  __construct($res,$type) {
        $this->res = $res;
        $this->type = in_array($type,array(PGSQL_ASSOC,PGSQL_BOTH,PGSQL_NUM))?$type:PGSQL_ASSOC;
    }

    public function rewind() {
        pg_result_seek($this->res,0);
        $this->p = 0;
        $this->length = pg_num_rows($this->res);
    }

    public function current() {
        return pg_fetch_array($this->res,$this->p,$this->type);
    }

    public function key() {
        return $this->p;
    }

    public function next() {
        $this->p++;
    }

    public function valid() {
        return ($this->p < $this->length);
    }

}
?>
