<?php
/**
 * @desc       Iterator для обработки результатов Выборки
 * @package    w framework
 * @category   engine/storage/db/mysqli
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Iterator, mysqli_result
 */
class DBIteratorMySQLi implements Iterator {

 private $res = null;
 private $type = MYSQLI_ASSOC;
 private $p = 0;

    public function  __construct(mysqli_result $res,$type) {
        $this->res = $res;
        $this->type = in_array($type,array(MYSQLI_ASSOC,MYSQLI_BOTH,MYSQLI_NUM))?$type:MYSQLI_ASSOC;
    }

    public function rewind() {
        $this->res->data_seek(0);
        $this->p = 0;
    }

    public function current() {
        return $this->res->fetch_array($this->type);
    }

    public function key() {
        return $this->p;
    }

    public function next() {
        $this->p++;
    }

    public function valid() {
        return ($this->p < $this->res->num_rows);
    }

}
?>
